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
use Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;

/**
 * Tracks account lifetime value.
 */
class ChannelDoctrineListener
{
    public const MAX_UPDATE_CHUNK_SIZE = 50;
    protected LifetimeHistoryStatusUpdateManager $statusUpdateManager;

    /** @var UnitOfWork */
    protected ?UnitOfWork $uow = null;
    protected ?EntityManager $em = null;
    protected ?LifetimeHistoryRepository $lifetimeRepo = null;
    protected ?CustomerRepository $customerRepo = null;

    protected array $queued = [];
    protected array $customerIdentities = [];
    protected bool $isInProgress = false;
    public function __construct(
        SettingsProvider $settingsProvider,
        LifetimeHistoryStatusUpdateManager $statusUpdateManager
    ) {
        $settings = $settingsProvider->getLifetimeValueSettings();
        foreach ($settings as $singleChannelTypeData) {
            $this->customerIdentities[$singleChannelTypeData['entity']] = $singleChannelTypeData['field'];
        }
        $this->statusUpdateManager = $statusUpdateManager;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->initializeFromEventArgs($args);
        foreach ($this->getChangedTrackedEntities() as $entity) {
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

            foreach (array_chunk($toOutDate, self::MAX_UPDATE_CHUNK_SIZE) as $records) {
                $this->statusUpdateManager->massUpdate($records);
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
     * @return \Generator
     */
    protected function getChangedTrackedEntities()
    {
        yield from $this->getCustomerIdentitiesEntities($this->uow->getScheduledEntityInsertions());
        yield from $this->getCustomerIdentitiesEntities($this->uow->getScheduledEntityDeletions());
        yield from $this->getCustomerIdentitiesEntities($this->uow->getScheduledEntityUpdates());
        yield from $this->getCustomerIdentitiesEntitiesFromCollectionChanges(
            $this->uow->getScheduledCollectionDeletions()
        );
        yield from $this->getCustomerIdentitiesEntitiesFromCollectionChanges(
            $this->uow->getScheduledCollectionUpdates()
        );
    }

    private function getCustomerIdentitiesEntitiesFromCollectionChanges($collections): \Generator
    {
        foreach ($collections as $collection) {
            if ($collection instanceof PersistentCollection) {
                yield from $this->getCustomerIdentitiesEntities($collection->unwrap()->toArray());
            } else {
                yield from $this->getCustomerIdentitiesEntities($collection);
            }
        }
    }

    private function getCustomerIdentitiesEntities(iterable $entities): \Generator
    {
        foreach ($entities as $entity) {
            if (!array_key_exists(ClassUtils::getClass($entity), $this->customerIdentities)) {
                continue;
            }

            yield $entity;
        }
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
            $this->lifetimeRepo = $this->em->getRepository(LifetimeValueHistory::class);
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
