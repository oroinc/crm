<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Analytics;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

class CustomerFrequencyProvider implements RFMProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $className
     */
    public function __construct(DoctrineHelper $doctrineHelper, $className)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return RFMMetricCategory::TYPE_FREQUENCY;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity)
    {
        return $entity instanceof RFMAwareInterface
            && $entity instanceof CustomerIdentityInterface
            && $entity instanceof $this->className;
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
                    $qb->expr()->eq($qb->expr()->lower('o.status'), ':status'),
                    $qb->expr()->eq('c.id', ':id'),
                    $qb->expr()->gte('o.createdAt', ':date')
                )
            )
            ->setParameter('status', Order::STATUS_SUCCESSFUL)
            ->setParameter('id', $this->doctrineHelper->getSingleEntityIdentifier($entity))
            ->setParameter('date', $date->sub(new \DateInterval('P365D')));

        $count = $qb->getQuery()->getSingleScalarResult();

        if (!$count) {
            return null;
        }

        return $count;
    }
}
