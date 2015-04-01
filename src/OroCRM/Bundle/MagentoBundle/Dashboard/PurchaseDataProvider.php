<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use DateTime;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class PurchaseDataProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ManagerRegistry $registry
     * @param ConfigProvider $configProvider
     */
    public function __construct(ManagerRegistry $registry, ConfigProvider $configProvider)
    {
        $this->registry = $registry;
        $this->configProvider = $configProvider;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return ChartView
     */
    public function getPurchaseChartView(ChartViewBuilder $viewBuilder, DateTime $from, DateTime $to)
    {
        $visited       = $this->getVisitedCount($from, $to);
        $deeplyVisited = $this->getDeeplyVisitedCount($from, $to);
        $addedToCart   = $this->getCartRepository()->getUniqueCustomerCarts($from, $to);
        $purchased     = $this->getOrderRepository()->getUniqueCustomersOrdersCount($from, $to);

        $items = [];

        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('revenue_over_time_chart')
        );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }

    /**
     * @return int
     */
    protected function getDeeplyVisitedCount(DateTime $from, DateTime $to)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder('t');

        return $qb
            ->select('COUNT(DISTINCT t.userIdentifier)')
            ->join('t.trackingWebsite', 'tw')
            ->join('tw.channel', 'c')
            ->andWhere('c.channelType = :channel')
            ->andWhere($qb->expr()->between('t.lastActionTime', ':from', ':to'))
            ->setParameters([
                'channel' => ChannelType::TYPE,
                'from'    => $from,
                'to'      => $to,
            ])
            ->groupBy('t.userIdentifier')
            ->andHaving('COUNT(t.userIdentifier) > 1')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return int
     */
    protected function getVisitedCount(DateTime $from, DateTime $to)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder('t');

        return $qb
            ->select('COUNT(DISTINCT t.userIdentifier)')
            ->join('t.trackingWebsite', 'tw')
            ->join('tw.channel', 'c')
            ->andWhere('c.channelType = :channel')
            ->andWhere($qb->expr()->between('t.lastActionTime', ':from', ':to'))
            ->setParameters([
                'channel' => ChannelType::TYPE,
                'from'    => $from,
                'to'      => $to,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CartRepository
     */
    protected function getCartRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Cart');
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Order');
    }

    /**
     * @return EntityRepository
     */
    protected function getTrackingVisitRepository()
    {
        return $this->registry->getRepository('OroTrackingBundle:TrackingVisit');
    }
}