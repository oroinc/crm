<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Analytics;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

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
     * @param RFMAwareInterface $entity
     *
     * {@inheritdoc}
     */
    public function getValue(RFMAwareInterface $entity)
    {
        $date = parent::getValue($entity);
        if (!$date) {
            return null;
        }

        $timezone = new \DateTimeZone('UTC');
        $now = new \DateTime('now', $timezone);

        return $now->diff(new \DateTime($date, $timezone))->days;
    }

    /**
     * @param Channel $dataChannel
     * @return array
     */
    protected function getValues(Channel $dataChannel)
    {
        $qb = $this->doctrineHelper
            ->getEntityRepository($this->className)
            ->createQueryBuilder('c');

        $qb
            ->select('MAX(o.createdAt) as value', 'c.id')
            ->join('c.orders', 'o')
            ->where($qb->expr()->andX(
                $qb->expr()->neq($qb->expr()->lower('o.status'), ':status'),
                $qb->expr()->eq('c.dataChannel', ':dataChannel')
            ))
            ->groupBy('c.id')
            ->setParameter('status', Order::STATUS_CANCELED)
            ->setParameter('dataChannel', $dataChannel);

        return $qb->getQuery()->getScalarResult();
    }
}
