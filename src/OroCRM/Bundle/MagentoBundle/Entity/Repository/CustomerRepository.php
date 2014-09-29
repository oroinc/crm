<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use Doctrine\ORM\EntityRepository;

class CustomerRepository extends EntityRepository
{
    /**
     * Returns data grouped by created_at, data_channel_id
     *
     * @param AclHelper $aclHelper
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param array     $ids Filter by channel ids
     *
     * @return array
     */
    public function getGroupedByChannelArray(
        AclHelper $aclHelper,
        \DateTime $dateFrom,
        \DateTime $dateTo = null,
        $ids = array()
    ) {
        $qb = $this->createQueryBuilder('c');
        $qb->select(
            'YEAR(c.createdAt) as yearCreated',
            'MONTH(c.createdAt) as monthCreated',
            'COUNT(c) as cnt',
            'IDENTITY(c.dataChannel) as channelId'
        );

        if ($dateTo) {
            $qb->andWhere($qb->expr()->between('c.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateTo', $dateTo);
        } else {
            $qb->andWhere('c.createdAt > :dateFrom');
        }

        $qb->setParameter('dateFrom', $dateFrom)
            ->groupBy('yearCreated', 'monthCreated', 'c.dataChannel');

        if ($ids) {
            $qb->andWhere($qb->expr()->in('c.dataChannel', ':channelIds'))
                ->setParameter('channelIds', $ids);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }
}
