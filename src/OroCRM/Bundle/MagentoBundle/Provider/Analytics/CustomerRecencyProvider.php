<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Analytics;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
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
        $qb = $this->doctrineHelper
            ->getEntityRepository($this->className)
            ->createQueryBuilder('c');

        $qb
            ->select('MAX(o.createdAt)')
            ->join('c.orders', 'o')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->neq($qb->expr()->lower('o.status'), ':status'),
                    $qb->expr()->eq('c.id', ':id')
                )
            )
            ->setParameter('status', Order::STATUS_CANCELED)
            ->setParameter('id', $this->doctrineHelper->getSingleEntityIdentifier($entity));

        $date = $qb->getQuery()->getSingleScalarResult();

        if (!$date) {
            return null;
        }

        $timezone = new \DateTimeZone('UTC');
        $now = new \DateTime('now', $timezone);

        return $now->diff(new \DateTime($date, $timezone))->days;
    }
}
