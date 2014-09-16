<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelDoctrineListener
{
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
        /** @var UnitOfWork $uow */
        $uow = $args->getEntityManager()->getUnitOfWork();

        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityDeletions(),
            $uow->getScheduledEntityUpdates()
        );

        $collections = array_merge(
            $uow->getScheduledCollectionDeletions(),
            $uow->getScheduledCollectionUpdates()
        );

        /** @var PersistentCollection $collectionToChange */
        foreach ($collections as $collectionToChange) {
            $entities = array_merge($entities, $collectionToChange->unwrap()->toArray());
        }

        foreach ($entities as $entity) {
            $className = ClassUtils::getClass($entity);

            if (array_key_exists($className, $this->customerIdentities)) {
                /** @var ChannelAwareInterface $entity */
                if ($uow->isScheduledForUpdate($entity)) {
                    $changeSet = $uow->getEntityChangeSet($entity);

                    $isUpdateRequired = array_key_exists('dataChannel', $changeSet)
                        || array_key_exists('account', $changeSet)
                        || array_key_exists($this->customerIdentities[$className], $changeSet);

                    if ($isUpdateRequired) {
                        $account = $entity->getAccount();
                        $channel = $entity->getDataChannel();
                        $this->scheduleUpdate($className, $entity, $account, $channel);

                        $oldChannel          = $this->getOldValue($changeSet, 'dataChannel');
                        $oldAccount          = $this->getOldValue($changeSet, 'account');
                        $extraUpdateRequired = $oldChannel || $oldAccount;
                        if ($extraUpdateRequired) {
                            $this->scheduleUpdate(
                                $className,
                                $entity,
                                $oldAccount ? : $account,
                                $oldChannel ? : $channel
                            );
                        }
                    }
                } else {
                    $this->scheduleUpdate($className, $entity, $entity->getAccount(), $entity->getDataChannel());
                }
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

        $this->isInProgress = true;
        $em                 = $args->getEntityManager();
        /** @var UnitOfWork $uow */
        $uow                = $em->getUnitOfWork();

        if (!empty($this->queued)) {
            foreach ($this->queued as $customerIdentity => $groupedByEntityUpdates) {
                foreach ($groupedByEntityUpdates as &$data) {

                    if(empty($data['account'])) {
                        $data['account'] = $this->getAccount($uow, $data);
                    }

                    $entity = $this->createHistoryEntry($em, $customerIdentity, $data);
                    $em->persist($entity);
                }
                unset($data);
            }

            $em->flush();

            $this->queued       = [];
            $this->isInProgress = false;
        }
    }

    /**
     * @param UnitOfWork $uow
     * @param array      $data
     *
     * @return mixed
     */
    protected function getAccount(UnitOfWork $uow, array $data)
    {
        $changeSet = $uow->getEntityChangeSet($data['entity']);

        if (array_key_exists('account', $changeSet)) {
            $account = !empty($changeSet['account'][0]) ? $changeSet['account'][0] : $changeSet['account'][1];
        } else {
            $account = $data['entity']->getAccount();
        }

        return ($account instanceof Account) ? $account->getid() : false;
    }

    /**
     * @param string  $customerIdentity
     * @param Object  $entity
     * @param Account $account
     * @param Channel $channel
     */
    protected function scheduleUpdate($customerIdentity, $entity, Account $account = null, Channel $channel = null)
    {
        if ($account && $channel) {
            $key = sprintf('%d__%d', $account->getId(), $channel->getId());
            $this->queued[$customerIdentity][$key] = [
                'account' => $account->getId(),
                'channel' => $channel->getId(),
                'entity'  => $entity
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
     * @param EntityManager $em
     * @param string        $customerIdentity
     * @param array         $data
     *
     * @return LifetimeValueHistory
     */
    protected function createHistoryEntry(EntityManager $em, $customerIdentity, array $data)
    {
        $this->setOldHistoryStatus($em, $data);

        $account = $em->getReference('OroCRMAccountBundle:Account', $data['account']);
        $channel = $em->getReference('OroCRMChannelBundle:Channel', $data['channel']);

        $history = new LifetimeValueHistory();
        $history->setAmount($this->calculateLifetime($em, $customerIdentity, $account, $channel));
        $history->setCreatedAt(new \DateTime('now'));
        $history->setDataChannel($channel);
        $history->setAccount($account);

        return $history;
    }

    /**
     * @param EntityManager $em
     * @param array         $data
     */
    protected function setOldHistoryStatus(EntityManager $em, array $data)
    {
        $account = $em->getReference('OroCRMAccountBundle:Account', $data['account']);
        $channel = $em->getReference('OroCRMChannelBundle:Channel', $data['channel']);

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb->update('OroCRMChannelBundle:LifetimeValueHistory', 'l');
        $qb->set('l.status', LifetimeValueHistory::STATUS_OLD);
        $qb->andWhere('l.account = :account');
        $qb->andWhere('l.dataChannel = :channel');
        $qb->setParameter('account', $account);
        $qb->setParameter('channel', $channel);
        $qb->getQuery()->execute();
    }

    /**
     * @param EntityManager $em
     * @param string        $customerIdentity
     * @param Account       $account
     * @param Channel       $channel
     *
     * @return mixed
     */
    protected function calculateLifetime(EntityManager $em, $customerIdentity, Account $account, Channel $channel)
    {
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb->from($customerIdentity, 'e');
        $qb->select(sprintf('SUM(e.%s)', $this->customerIdentities[$customerIdentity]));
        $qb->andWhere('e.account = :account');
        $qb->andWhere('e.channel = :channel');
        $qb->setParameter('account', $account);
        $qb->setParameter('channel', $channel);

        $result = $qb->getQuery()->getSingleScalarResult();

        return empty($result) ? $result : 0;
    }
}
