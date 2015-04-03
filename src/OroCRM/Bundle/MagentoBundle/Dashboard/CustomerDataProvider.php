<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;

class CustomerDataProvider
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

    /** @var DateHelper */
    protected $dateHelper;

    /**
     * @param ManagerRegistry $registry
     * @param AclHelper       $aclHelper
     * @param ConfigProvider  $configProvider
     * @param DateHelper      $dateHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        ConfigProvider $configProvider,
        DateHelper $dateHelper
    ) {
        $this->registry       = $registry;
        $this->aclHelper      = $aclHelper;
        $this->configProvider = $configProvider;
        $this->dateHelper     = $dateHelper;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param array            $dateRange
     *
     * @return ChartView
     */
    public function getNewCustomerChartView(ChartViewBuilder $viewBuilder, $dateRange)
    {
        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->registry->getRepository('OroCRMMagentoBundle:Customer');

        /** @var ChannelRepository $channelRepository */
        $channelRepository = $this->registry->getRepository('OroCRMChannelBundle:Channel');
        $now               = $dateRange['end'];
        $past              = $dateRange['start'];
        $items             = [];

        // get all integration channels
        $channels   = $channelRepository->getAvailableChannelNames($this->aclHelper, 'magento');
        $channelIds = array_keys($channels);
        $data       = $customerRepository->getGroupedByChannelArray(
            $this->aclHelper,
            $past,
            $now,
            $channelIds,
            $this->dateHelper
        );

        $dates = $this->dateHelper->getDatePeriod($past, $now);

        foreach ($data as $row) {
            $key         = $this->dateHelper->getKey($past, $now, $row);
            $channelId   = (int)$row['channelId'];
            $channelName = $channels[$channelId]['name'];

            if (!isset($items[$channelName])) {
                $items[$channelName] = $dates;
            }
            $items[$channelName][$key]['cnt'] = (int)$row['cnt'];
        }

        // restore default keys
        foreach ($items as $channelName => $item) {
            $items[$channelName] = array_values($item);
        }
        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('new_web_customers')
        );

        $chartType = $this->dateHelper->getFormatStrings($past, $now)['viewType'];
        $chartOptions['data_schema']['label']['type']  = $chartType;
        $chartOptions['data_schema']['label']['label'] =
            sprintf(
                'oro.dashboard.chart.%s.label',
                $chartType
            );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }
}
