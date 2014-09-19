<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

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
        \DateTime $dateTo,
        $ids = array()
    ) {
        $qb = $this->createQueryBuilder('c');
        $qb->select(
            "DATE(CONCAT(YEAR(c.createdAt), '-', MONTH(c.createdAt), '-01')) as formattedDate",
            'COUNT(c) as cnt',
            'IDENTITY(c.dataChannel)',
            'c.createdAt'
        )
            ->andWhere($qb->expr()->between('c.createdAt', ':dateFrom', ':dateTo'))
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->groupBy('formattedDate', 'c.dataChannel');

        if ($ids) {
            $qb->andWhere($qb->expr()->in('c.dataChannel', ':channelIds'))
                ->setParameter('channelIds', $ids);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }
}
