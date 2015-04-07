<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerRepository extends EntityRepository
{
    /**
     * Returns data grouped by created_at, data_channel_id
     *
     * @param AclHelper  $aclHelper
     * @param \DateTime  $dateFrom
     * @param \DateTime  $dateTo
     * @param array      $ids Filter by channel ids
     * @param DateHelper $dateHelper
     *
     * @return array
     */
    public function getGroupedByChannelArray(
        AclHelper $aclHelper,
        \DateTime $dateFrom,
        \DateTime $dateTo = null,
        $ids = [],
        DateHelper $dateHelper
    ) {
        $qb = $this->createQueryBuilder('c');
        $qb->select(
            'COUNT(c) as cnt',
            'IDENTITY(c.dataChannel) as channelId'
        );
        $dateHelper->addDatePartsSelect($dateFrom, $dateTo, $qb, 'c.createdAt');

        if ($dateTo) {
            $qb->andWhere($qb->expr()->between('c.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateTo', $dateTo);
        } else {
            $qb->andWhere('c.createdAt > :dateFrom');
        }

        $qb->setParameter('dateFrom', $dateFrom);
        $qb->addGroupBy('c.dataChannel');

        if ($ids) {
            $qb->andWhere($qb->expr()->in('c.dataChannel', ':channelIds'))
                ->setParameter('channelIds', $ids);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param Customer $customer
     * @param string   $value
     */
    public function updateCustomerLifetimeValue(Customer $customer, $value)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->update('OroCRMMagentoBundle:Customer', 'c')
            ->set('c.lifetime', 'c.lifetime + :value')
            ->setParameter('value', $value)
            ->where('c.id = :id')
            ->setParameter('id', $customer->getId());

        $qb->getQuery()->execute();
    }
}
