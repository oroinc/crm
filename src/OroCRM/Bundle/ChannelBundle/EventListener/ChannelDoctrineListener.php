<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;

class ChannelDoctrineListener
{
    const MAX_UPDATE_CHUNK_SIZE = 50;

    /** @var UnitOfWork */
    protected $uow;

    /** @var EntityManager */
    protected $em;

    /** @var LifetimeHistoryRepository */
    protected $lifetimeRepo;

    /** @var array */
    protected $queued = [];

    /** @var array */
    protected $customerIdentities = [];

    /** @var bool */
    protected $isInProgress = false;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
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

        $entitiesToTrack = $this->customerIdentities;
        $entities        = array_filter(
            $entities,
            function ($entity) use ($entitiesToTrack) {
                return $entity instanceof CustomerIdentityInterface
                && array_key_exists(ClassUtils::getClass($entity), $entitiesToTrack);
            }
        );

        /** @var CustomerIdentityInterface $entity */
        foreach ($entities as $entity) {
            $className = ClassUtils::getClass($entity);
            if ($this->uow->isScheduledForUpdate($entity)) {
                $changeSet = $this->uow->getEntityChangeSet($entity);

                $isUpdateRequired = array_key_exists('dataChannel', $changeSet)
                    || array_key_exists('account', $changeSet)
                    || array_key_exists($this->customerIdentities[$className], $changeSet);

                if ($isUpdateRequired) {
                    $account = $entity->getAccount();
                    $channel = $entity->getDataChannel();
                    $this->scheduleUpdate($className, $account, $channel);

                    $oldChannel          = $this->getOldValue($changeSet, 'dataChannel');
                    $oldAccount          = $this->getOldValue($changeSet, 'account');
                    $extraUpdateRequired = $oldChannel || $oldAccount;
                    if ($extraUpdateRequired) {
                        $this->scheduleUpdate($className, $oldAccount ?: $account, $oldChannel ?: $channel);
                    }
                }
            } else {
                $this->scheduleUpdate($className, $entity->getAccount(), $entity->getDataChannel());
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

        if (!empty($this->queued)) {
            $toOutDate = [];

            foreach ($this->queued as $customerIdentity => $groupedByEntityUpdates) {
                foreach ($groupedByEntityUpdates as $data) {
                    $account = is_object($data['account'])
                        ? $data['account']
                        : $this->em->getReference('OroCRMAccountBundle:Account', $data['account']);

                    $channel = is_object($data['channel'])
                        ? $data['channel']
                        : $this->em->getReference('OroCRMChannelBundle:Channel', $data['channel']);

                    $entity      = $this->createHistoryEntry($customerIdentity, $account, $channel);
                    $toOutDate[] = [$account, $channel, $entity];

                    $this->em->persist($entity);
                }
            }

            $this->isInProgress = true;

            $this->em->flush();

            foreach (array_chunk($toOutDate, self::MAX_UPDATE_CHUNK_SIZE) as $chunks) {
                $this->lifetimeRepo->massStatusUpdate($chunks);
            }

            $this->queued       = [];
            $this->isInProgress = false;
        }
    }

    /**
     * @param string  $customerIdentity
     * @param Account $account
     * @param Channel $channel
     */
    protected function scheduleUpdate($customerIdentity, Account $account = null, Channel $channel = null)
    {
        if ($account && $channel) {
            // skip removal, history items will be flushed by FK constraints
            if ($this->uow->isScheduledForDelete($account) || $this->uow->isScheduledForDelete($channel)) {
                return;
            }

            $key = sprintf('%s__%s', spl_object_hash($account), spl_object_hash($channel));

            $this->queued[$customerIdentity][$key] = [
                'account' => $account->getId() ?: $account,
                'channel' => $channel->getId() ?: $channel,
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
     * @param string $customerIdentity
     * @param Account $account
     * @param Channel $channel
     *
     * @return LifetimeValueHistory
     */
    protected function createHistoryEntry($customerIdentity, Account $account, Channel $channel)
    {
        $lifetimeAmount = $this->lifetimeRepo->calculateAccountLifetime(
            $customerIdentity,
            $this->customerIdentities[$customerIdentity],
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
     * @param PostFlushEventArgs|OnFlushEventArgs $args
     */
    protected function initializeFromEventArgs($args)
    {
        $this->em           = $args->getEntityManager();
        $this->uow          = $this->em->getUnitOfWork();
        $this->lifetimeRepo = $this->em->getRepository('OroCRMChannelBundle:LifetimeValueHistory');
    }

    /**
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event)
    {
        $this->queued = [];
    }
}
