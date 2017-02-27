<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;

class ChannelDoctrineListener
{
    const MAX_UPDATE_CHUNK_SIZE = 50;

    /** @var UnitOfWork */
    protected $uow;

    /** @var EntityManager */
    protected $em;

    /** @var LifetimeHistoryRepository */
    protected $lifetimeRepo;

    /** @var CustomerRepository */
    protected $customerRepo;

    /** @var array */
    protected $queued = [];

    /** @var array */
    protected $customerIdentities = [];

    /** @var bool */
    protected $isInProgress = false;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(
        SettingsProvider $settingsProvider
    ) {
        $settings = $settingsProvider->getLifetimeValueSettings();
        foreach ($settings as $singleChannelTypeData) {
            $this->customerIdentities[$singleChannelTypeData['entity']] = $singleChannelTypeData['field'];
        }
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->initializeFromEventArgs($args);
        $entities = $this->getChangedTrackedEntities();

        foreach ($entities as $entity) {
            if ($this->uow->isScheduledForUpdate($entity)) {
                $this->checkAndUpdate($entity, $this->uow->getEntityChangeSet($entity));
            } else {
                $account = $this->getAccount($entity);
                $this->scheduleUpdate(
                    $account,
                    $entity->getDataChannel()
                );
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->isInProgress) {
            return;
        }

        $this->initializeFromEventArgs($args);

        if (count($this->queued) > 0) {
            $toOutDate = [];

            foreach ($this->queued as $data) {
                /** @var Account $account */
                $account = is_object($data['account'])
                    ? $data['account']
                    : $this->em->getReference('OroAccountBundle:Account', $data['account']);

                /** @var Channel $channel */
                $channel = is_object($data['channel'])
                    ? $data['channel']
                    : $this->em->getReference('OroChannelBundle:Channel', $data['channel']);

                $entity      = $this->createHistoryEntry($account, $channel);
                $toOutDate[] = [$account, $channel, $entity];

                $this->em->persist($entity);
            }

            $this->isInProgress = true;

            $this->em->flush();

            foreach (array_chunk($toOutDate, self::MAX_UPDATE_CHUNK_SIZE) as $chunks) {
                $this->getLifetimeRepository()->massStatusUpdate($chunks);
            }

            $this->queued       = [];
            $this->isInProgress = false;
        }
    }

    /**
     * @param object       $customerIdentityEntity
     * @param Account|null $account
     * @param Channel|null $channel
     */
    public function scheduleEntityUpdate($customerIdentityEntity, Account $account = null, Channel $channel = null)
    {
        if (!$this->uow) {
            throw new \RuntimeException('UOW is missing, listener is not initialized');
        }

        $this->scheduleUpdate($account, $channel);
    }

    /**
     * @param PostFlushEventArgs|OnFlushEventArgs $args
     */
    public function initializeFromEventArgs($args)
    {
        $this->em  = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();
    }

    /**
     * @return array|[]
     */
    protected function getChangedTrackedEntities()
    {
        $entities = array_merge(
            $this->uow->getScheduledEntityInsertions(),
            $this->uow->getScheduledEntityDeletions(),
            $this->uow->getScheduledEntityUpdates()
        );

        $collections = array_merge(
            $this->uow->getScheduledCollectionDeletions(),
            $this->uow->getScheduledCollectionUpdates()
        );

        /** @var PersistentCollection $collectionToChange */
        foreach ($collections as $collectionToChange) {
            $entities = array_merge($entities, $collectionToChange->unwrap()->toArray());
        }

        return array_filter(
            $entities,
            function ($entity) {
                return array_key_exists(ClassUtils::getClass($entity), $this->customerIdentities);
            }
        );
    }

    /**
     * @param object $entity
     * @param array $changeSet
     */
    protected function checkAndUpdate($entity, array $changeSet)
    {
        $className = ClassUtils::getClass($entity);

        if ($this->isUpdateRequired($className, $changeSet)) {
            $account = $this->getAccount($entity);
            $channel = $entity->getDataChannel();
            $this->scheduleUpdate($account, $channel);

            $oldChannel = $this->getOldValue($changeSet, 'dataChannel');
            $oldAccount = $this->getOldValue($changeSet, 'account');
            if ($oldChannel || $oldAccount) {
                $this->scheduleUpdate($oldAccount ? : $account, $oldChannel ? : $channel);
            }
        }
    }

    /**
     * @param string $className
     * @param array  $changeSet
     * @return bool
     */
    protected function isUpdateRequired($className, array $changeSet)
    {
        return array_key_exists('dataChannel', $changeSet)
        || array_key_exists('account', $changeSet)
        || array_key_exists($this->customerIdentities[$className], $changeSet);
    }

    /**
     * @param Account $account
     * @param Channel $channel
     */
    protected function scheduleUpdate(Account $account = null, Channel $channel = null)
    {
        if ($account && $channel) {
            // skip removal, history items will be flushed by FK constraints
            if ($this->uow->isScheduledForDelete($account) || $this->uow->isScheduledForDelete($channel)) {
                return;
            }

            $key = sprintf('%s__%s', spl_object_hash($account), spl_object_hash($channel));

            $this->queued[$key] = [
                'account' => $account->getId() ? : $account,
                'channel' => $channel->getId() ? : $channel
            ];
        }
    }

    /**
     * Returns value before change, or null otherwise
     *
     * @param array  $changeSet
     * @param string $key
     *
     * @return null|object
     */
    protected function getOldValue(array $changeSet, $key)
    {
        return array_key_exists($key, $changeSet) ? $changeSet[$key][0] : null;
    }

    /**
     * @param Account $account
     * @param Channel $channel
     *
     * @return LifetimeValueHistory
     */
    protected function createHistoryEntry(Account $account, Channel $channel)
    {
        $lifetimeAmount = $this->getLifetimeRepository()->calculateAccountLifetime(
            $this->customerIdentities,
            $account,
            $channel
        );

        $history = new LifetimeValueHistory();
        $history->setAmount($lifetimeAmount);
        $history->setDataChannel($channel);
        $history->setAccount($account);

        return $history;
    }

    /**
     * @return LifetimeHistoryRepository
     */
    protected function getLifetimeRepository()
    {
        if (null === $this->lifetimeRepo) {
            $this->lifetimeRepo = $this->em->getRepository('OroChannelBundle:LifetimeValueHistory');
        }

        return $this->lifetimeRepo;
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        if (null === $this->customerRepo) {
            $this->customerRepo = $this->em->getRepository('OroSalesBundle:Customer');
        }

        return $this->customerRepo;
    }

    /**
     * @param object $entity
     * @return null|Account
     */
    protected function getAccount($entity)
    {
        $identityFQCN = ClassUtils::getClass($entity);
        $field = AccountCustomerManager::getCustomerTargetField($identityFQCN);
        $customer = $this->getCustomerRepository()->getCustomerByTarget($entity->getId(), $field);
        if (!$customer) {
            return null;
        }

        return $customer->getAccount();
    }
}
