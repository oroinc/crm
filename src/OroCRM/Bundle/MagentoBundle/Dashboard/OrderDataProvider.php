<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

class OrderDataProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @param ManagerRegistry $registry
     * @param AclHelper $aclHelper
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        ConfigProvider $configProvider
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @return ChartView
     */
    public function getAverageOrderAmountChartView(ChartViewBuilder $viewBuilder)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->registry->getRepository('OroCRMMagentoBundle:Order');
        $result = $orderRepository->getAverageOrderAmount($this->aclHelper);

        // prepare chart items
        $items = [];
        foreach ($result as $channel) {
            $channelName = $channel['name'];
            $channelData = $channel['data'];

            $items[$channelName] = [];

            foreach ($channelData as $year => $monthData) {
                foreach ($monthData as $month => $amount) {
                    $items[$channelName][] = [
                        'month' => sprintf('%04d-%02d-01', $year, $month),
                        'amount' => $amount
                    ];
                }
            }
        }

        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('average_order_amount')
        );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }
}
