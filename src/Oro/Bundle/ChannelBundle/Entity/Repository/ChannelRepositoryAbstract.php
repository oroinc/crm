<?php

namespace Oro\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\ChannelBundle\Entity\Channel;

abstract class ChannelRepositoryAbstract extends EntityRepository implements ChannelRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAvailableChannelNames(AclHelper $aclHelper, $type = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c.id', 'c.name');
        $qb->from('OroChannelBundle:Channel', 'c', 'c.id');

        if (null !== $type) {
            $qb->andWhere($qb->expr()->eq('c.channelType', ':type'));
            $qb->setParameter('type', $type);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getVisitsCountByPeriodForChannelType(
        \DateTime $start,
        \DateTime $end,
        AclHelper $aclHelper,
        $type
    ) {
        @trigger_error(
            'getVisitsCountByPeriodForChannelType() is deprecated since version 2.0, to be removed in 3.0.',
            E_USER_DEPRECATED
        );

        $qb = $this->_em->createQueryBuilder();

        $qb->select('COUNT(visit.id)')
            ->from('OroTrackingBundle:TrackingVisit', 'visit')
            ->join('visit.trackingWebsite', 'site')
            ->leftJoin('site.channel', 'channel')
            ->where($qb->expr()->orX(
                $qb->expr()->isNull('channel.id'),
                $qb->expr()->andX(
                    $qb->expr()->eq('channel.channelType', ':type')
                )
            ))
            ->andWhere($qb->expr()->between('visit.firstActionTime', ':dateStart', ':dateEnd'))
            ->setParameter('type', $type)
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);

        return (int) $aclHelper->apply($qb)->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelsByEntities(
        array $entities = [],
        $status = Channel::STATUS_ACTIVE,
        AclHelper $aclHelper = null
    ) {
        $query = $this->getChannelsByEntitiesQB($entities, $status)->getQuery();

        if ($aclHelper) {
            return $aclHelper->apply($query)->getResult();
        }

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelsByEntitiesQB(array $entities = [], $status = Channel::STATUS_ACTIVE)
    {
        $query = $this->createQueryBuilder('c');
        if (!empty($entities)) {
            $countDistinctName = $query->expr()->eq($query->expr()->countDistinct('e.name'), ':count');

            $query->innerJoin('c.entities', 'e');
            $query->andWhere($query->expr()->in('e.name', ':entitiesNames'));
            $query->setParameter('entitiesNames', $entities);
            $query->groupBy('c.name', 'c.id');
            $query->having($countDistinctName);
            $query->setParameter('count', count($entities));
        }
        $query->orderBy('c.name', 'ASC');

        return $query;
    }
}
