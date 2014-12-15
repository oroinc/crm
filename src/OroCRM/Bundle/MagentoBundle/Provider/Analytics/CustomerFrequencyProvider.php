<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Analytics;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

class CustomerFrequencyProvider extends AbstractCustomerRFMProvider
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return RFMMetricCategory::TYPE_FREQUENCY;
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

        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $qb
            ->select('COUNT(o)')
            ->join('c.orders', 'o')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->neq($qb->expr()->lower('o.status'), ':status'),
                    $qb->expr()->eq('c.id', ':id'),
                    $qb->expr()->gte('o.createdAt', ':date')
                )
            )
            ->setParameter('status', Order::STATUS_CANCELED)
            ->setParameter('id', $this->doctrineHelper->getSingleEntityIdentifier($entity))
            ->setParameter('date', $date->sub(new \DateInterval('P365D')));

        $count = $qb->getQuery()->getSingleScalarResult();

        if (!$count) {
            return null;
        }

        return $count;
    }
}
