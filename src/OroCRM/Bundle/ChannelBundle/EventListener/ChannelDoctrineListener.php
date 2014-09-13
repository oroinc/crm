<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Query;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
        $changeSet          = $this->uow->getEntityChangeSet($entity);
        $lifetimeValueQuery = [];

        if (
            $isUpdate &&
            (
                (!empty($changeSet['account']) && !empty($changeSet['account'][0]))
                || !empty($changeSet['dataChannel']) && !empty($changeSet['dataChannel'][0])
            )
        ) {
            $account = $changeSet['account'][0];
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
            $account = $changeSet['account'][1];
            $dataChannel = $changeSet['dataChannel'][1];

            $entityParam   = [
                'account' => $account,
                'channel' => $dataChannel
            ];
            $lifetimeValue = $this->getLifetimeValue($config, $entityParam);
        }

        $currentLifetimeValue = $this->getLifetimeValueFromEntity($entity, $config);

        $currentLifetime = 0;

        if (is_array($lifetimeValue)) {
            foreach ($currentLifetimeValue as $row) {
                $currentLifetime += $row;
            }
        } else {
            $currentLifetime = $currentLifetimeValue;
        }

        $currentLifetime = $currentLifetime + $currentLifetimeValue;

        #$amountFromQuery + $currentEntity ->getLEFITIMEFIELD

        $this->createHistory($account, $dataChannel, $currentLifetime);
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

        return $qb->getQuery()->execute();
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

    protected function createHistory($channel, $account, $amount)
    {
        $history = new LifetimeValueHistory();
        $history->setDataChannel($channel);
        $history->setAccount($account);
        $history->setAmount($amount);
    }
}
