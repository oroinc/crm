<?php

namespace Oro\Bundle\MagentoBundle\Provider\Analytics;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Order;

/**
 * Calculates the "monetary" RFM metric.
 */
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
     * {@inheritdoc}
     */
    protected function getScalarValues(Channel $channel, array $ids = [])
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
                    $qb->expr()->eq('c.dataChannel', ':channel')
                )
            )
            ->groupBy('c.id')
            ->orderBy($qb->expr()->asc('c.id'))
            ->setParameter('status', Order::STATUS_CANCELED)
            ->setParameter('channel', $channel)
            ->setParameter('date', $date->sub(new \DateInterval('P365D')), Types::DATETIME_MUTABLE);

        if (!empty($ids)) {
            $qb->andWhere($qb->expr()->in('c.id', ':ids'))
                ->setParameter('ids', $ids);
        }

        return $qb->getQuery()->getScalarResult();
    }
}
