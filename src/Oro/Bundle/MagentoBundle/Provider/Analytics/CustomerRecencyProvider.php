<?php

namespace Oro\Bundle\MagentoBundle\Provider\Analytics;

use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Order;

class CustomerRecencyProvider extends AbstractCustomerRFMProvider
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return RFMMetricCategory::TYPE_RECENCY;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScalarValues(Channel $channel, array $ids = [])
    {
        $qb = $this->doctrineHelper
            ->getEntityRepository($this->className)
            ->createQueryBuilder('c');

        $qb
            ->select('MAX(o.createdAt) as value', 'c.id')
            ->join('c.orders', 'o')
            ->where($qb->expr()->andX(
                $qb->expr()->neq($qb->expr()->lower('o.status'), ':status'),
                $qb->expr()->eq('c.dataChannel', ':channel')
            ))
            ->groupBy('c.id')
            ->setParameter('status', Order::STATUS_CANCELED)
            ->setParameter('channel', $channel);

        if (!empty($ids)) {
            $qb->andWhere($qb->expr()->in('c.id', ':ids'))
                ->setParameter('ids', $ids);
        }

        $timezone = new \DateTimeZone('UTC');
        $now = new \DateTime('now', $timezone);

        return array_map(function ($value) use ($now, $timezone) {
            $value['value'] = $now->diff(new \DateTime($value['value'], $timezone))->days;
            return $value;
        }, $qb->getQuery()->getScalarResult());
    }
}
