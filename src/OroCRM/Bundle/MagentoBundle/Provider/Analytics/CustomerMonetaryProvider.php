<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Analytics;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

class CustomerMonetaryProvider extends AbstractCustomerRFMProvider
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return RFMMetricCategory::TYPE_MONETARY;
    }

    /**
     * @param Channel $dataChannel
     * @return array
     */
    protected function getValues(Channel $dataChannel)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $qb = $this->doctrineHelper
            ->getEntityRepository($this->className)
            ->createQueryBuilder('c');

        $qb
            ->select('SUM(
                CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
                CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
                ) as value', 'c.id')
            ->join('c.orders', 'o')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->neq($qb->expr()->lower('o.status'), ':status'),
                    $qb->expr()->gte('o.createdAt', ':date'),
                    $qb->expr()->gte('c.dataChannel', ':dataChannel')
                )
            )
            ->groupBy('c.id')
            ->setParameter('status', Order::STATUS_CANCELED)
            ->setParameter('dataChannel', $dataChannel)
            ->setParameter('date', $date->sub(new \DateInterval('P365D')));

        return $qb->getQuery()->getScalarResult();
    }
}
