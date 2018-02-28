<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitEventProviderInterface;

class TrackingVisitEventProvider implements TrackingVisitEventProviderInterface, FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry  = $registry;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerEventAggregates(array $customers, array $eventNames)
    {
        if ($this->isFeaturesEnabled()) {
            return [];
        }

        $qb = $this->getEventsQueryBuilder($customers);

        $qb->select('e.name, COUNT(we) cnt, MAX(we.loggedAt) last')
            ->leftJoin('tve.event', 'e')
            ->andWhere('e.name IN (:event_names)')
            ->groupBy('e.name')
            ->setParameter('event_names', $eventNames);

        $aggregates = $qb->getQuery()->getResult();

        $metrics = [];
        foreach ($aggregates as $agg) {
            $metrics[$agg['name']] = [
                'count' => (int) $agg['cnt'],
                'last' => $agg['last'],
            ];
        }

        return $metrics;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerEventsCountByDate(array $customers, array $eventNames)
    {
        if ($this->isFeaturesEnabled()) {
            return [];
        }

        $qb = $this->getEventsQueryBuilder($customers);

        $qb->select('e.name, COUNT(we) cnt, DATE(we.loggedAt) date')
            ->leftJoin('tve.event', 'e')
            ->andWhere('e.name IN (:event_names)')
            ->groupBy('e.name, date')
            ->orderBy('date', 'ASC')
            ->setParameter('event_names', $eventNames);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getCustomerEventsCountByDateAndChannel(array $customers, array $eventNames)
    {
        if ($this->isFeaturesEnabled()) {
            return [];
        }

        $qb = $this->getEventsQueryBuilder($customers);

        $qb->select('e.name, COUNT(we) cnt, DATE(we.loggedAt) date, wsc.name channel')
            ->leftJoin('tve.event', 'e')
            ->leftJoin('tve.website', 'ws')
            ->leftJoin('ws.channel', 'wsc')
            ->andWhere('e.name IN (:event_names)')
            ->groupBy('channel, e.name, date')
            ->addOrderBy('e.name', 'ASC')
            ->addOrderBy('wsc.name', 'ASC')
            ->addOrderBy('date', 'ASC')
            ->setParameter('event_names', $eventNames);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritdoc
     */
    public function getMostViewedPage(array $customers = [])
    {
        if ($this->isFeaturesEnabled()) {
            return null;
        }

        $qb = $this->getEventsQueryBuilder($customers);
        $qb->select('we.title, we.url, COUNT(we) cnt')
            ->groupBy('we.url, we.title')
            ->orderBy('cnt', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getScalarResult();
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @inheritdoc
     */
    public function getLastViewedPage(array $customers = [])
    {
        if ($this->isFeaturesEnabled()) {
            return null;
        }

        $qb = $this->getEventsQueryBuilder($customers);
        $qb->select('we.title, we.url')
            ->orderBy('we.loggedAt', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getScalarResult();
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Customer[] $customers
     * @return QueryBuilder
     */
    protected function getEventsQueryBuilder(array $customers)
    {
        $customerAssocName = ExtendHelper::buildAssociationName(Customer::class, 'association');

        return $this->registry
            ->getRepository('OroTrackingBundle:TrackingVisitEvent')
            ->createQueryBuilder('tve')
            ->leftJoin('tve.webEvent', 'we')
            ->andWhere(sprintf('tve.%s in (:customers)', $customerAssocName))
            ->setParameter('customers', $customers);
    }
}
