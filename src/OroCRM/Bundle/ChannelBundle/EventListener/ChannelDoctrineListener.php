<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Query;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelDoctrineListener
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var PropertyAccess */
    protected $accessor;

    /** @var OroEntityManager */
    protected $em;

    /** @var UnitOfWork */
    protected $uow;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
        $this->accessor         = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $settings  = $this->settingsProvider->getChannelTypeLifetimeValue();
        $this->em  = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            $className = ClassUtils::getClass($entity);
            $config    = $this->searchIn($className, $settings);

            if (!empty($config)) {
                $this->update($entity, $config);
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            $className = ClassUtils::getClass($entity);
            $config    = $this->searchIn($className, $settings);

            if (!empty($config)) {
                $this->update($entity, $config, true);
            }
        }
    }

    /**
     * @param string $className
     * @param array  $settings
     *
     * @return array
     */
    protected function searchIn($className, $settings)
    {
        foreach ($settings as $row) {
            if ($row['customer_identity'] === $className) {
                return $row;
            }
        }
        return [];
    }

    /**
     * @param Object $entity
     * @param array  $config
     * @param bool   $isUpdate
     */
    protected function update($entity, array $config, $isUpdate = false)
    {
        $changeSet = $this->uow->getEntityChangeSet($entity);

        if ($this->isUpdate($changeSet, $isUpdate, $config)) {
            $account     = $changeSet['account'][0];
            $dataChannel = $changeSet['dataChannel'][0];
            $entityParam = [
                'account' => $account,
                'channel' => $dataChannel,
                'id'      => $entity->getId()
            ];

            if ($entity->getId()) {
                $entityParam['id'] = $entity->getId();
            }

            $lifetimeValue = $this->getLifetimeValue($config, $entityParam);
        } else {
            $account       = $changeSet['account'][1];
            $dataChannel   = $changeSet['dataChannel'][1];
            $entityParam   = [
                'account' => $account,
                'channel' => $dataChannel
            ];
            $lifetimeValue = $this->getLifetimeValue($config, $entityParam);
        }

        $currentLifetime = $this->calculateLifeTime($entity, $config, $lifetimeValue);

        $this->createHistory($account, $dataChannel, $currentLifetime);
    }

    /**
     * @param Object $entity
     * @param array  $config
     * @param mixed  $lifetimeValue
     *
     * @return int
     */
    protected function calculateLifeTime($entity, array $config, $lifetimeValue)
    {
        $entityLifetimeValue = $this->getLifetimeValueFromEntity($entity, $config);
        $currentLifetime     = 0;

        if (is_array($lifetimeValue)) {
            foreach ($entityLifetimeValue as $row) {
                $currentLifetime += $row;
            }
        } else {
            $currentLifetime = $entityLifetimeValue;
        }

        return  $currentLifetime;
    }

    /**
     * @param array $changeSet
     * @param bool  $isUpdate
     * @param array $config
     *
     * @return bool
     */
    protected function isUpdate(array $changeSet, $isUpdate, array $config)
    {
        $lifetimeValue = !empty($config['lifetime_value']) ? $config['lifetime_value'] : false;

        return $isUpdate &&
        (
            $this->isChanged($changeSet, 'account')
            || $this->isChanged($changeSet, 'dataChannel')
            || $this->isChanged($changeSet, $lifetimeValue)
        );
    }

    /**
     * @param array $config
     * @param array $entityParam
     *
     * @return Query
     */
    protected function getLifetimeValue(array $config, array $entityParam)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->createQueryBuilder();
        $qb->add(
            'select',
            'SUM(e.' . $config['lifetime_value'] . ')'
        );
        $qb->from($config['customer_identity'], 'e');
        $qb->andWhere('e.account = :account');
        $qb->andWhere('e.channel = :channel');

        if (!empty($entityParam['id'])) {
            $qb->andWhere('e.id <> :id');
        }

        $qb->setParameter('account', $entityParam['account']);
        $qb->setParameter('channel', $entityParam['channel']);

        if (!empty($entityParam['id'])) {
            $qb->setParameter('id', $entityParam['id']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Object $entity
     * @param array  $config
     *
     * @return int|mixed
     */
    protected function getLifetimeValueFromEntity($entity, $config)
    {
        $result = 0;

        try {
            $result = $this->accessor->getValue($entity, $config['lifetime_value']);
        } catch (Exception $e) {

        }

        return $result;
    }

    /**
     * @param Channel $channel
     * @param Account $account
     * @param int     $amount
     */
    protected function createHistory(Channel $channel, Account $account, $amount = 0)
    {
        $history = new LifetimeValueHistory();
        $history->setDataChannel($channel);
        $history->setAccount($account);
        $history->setAmount($amount);

        $this->em->persist($history);
        $this->em->flush();
    }

    /**
     * @param array  $changeSet
     * @param string $field
     *
     * @return bool
     */
    private function isChanged(array $changeSet, $field)
    {
        $oldValue = (!empty($changeSet[$field][0])) ? $changeSet[$field][0] : false;
        $newValue = (!empty($changeSet[$field][1])) ? $changeSet[$field][1] : false;

        return ($oldValue !== $newValue);
    }
}
