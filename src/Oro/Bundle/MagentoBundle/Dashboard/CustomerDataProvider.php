<?php

namespace Oro\Bundle\MagentoBundle\Dashboard;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CustomerDataProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var ConfigProvider */
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
        $customerRepository = $this->registry->getRepository('OroMagentoBundle:Customer');

        /** @var ChannelRepository $channelRepository */
        $channelRepository = $this->registry->getRepository('OroChannelBundle:Channel');
        list($past, $now)  = $this->dateHelper->getPeriod($dateRange, 'OroMagentoBundle:Customer', 'createdAt');
        if ($past === null && $now === null) {
            $past = new \DateTime(DateHelper::MIN_DATE, new \DateTimeZone('UTC'));
            $now   = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $items             = [];

        /**
         * @todo Remove dependency on exact magento channel type in CRM-8153
         */
        // get all integration channels
        $channels   = $channelRepository->getAvailableChannelNames($this->aclHelper, MagentoChannelType::TYPE);
        $channelIds = array_keys($channels);
        $dates = $this->dateHelper->getDatePeriod($past, $now);
        $data  = $customerRepository->getGroupedByChannelArray(
            $this->aclHelper,
            $this->dateHelper,
            $past,
            $now,
            $channelIds
        );

        foreach ($data as $row) {
            $key         = $this->dateHelper->getKey($past, $now, $row);
            $channelId   = (int)$row['channelId'];
            $channelName = $channels[$channelId]['name'];

            if (!isset($items[$channelName])) {
                $items[$channelName] = $dates;
            }

            if (isset($items[$channelName][$key])) {
                $items[$channelName][$key]['cnt'] = (int)$row['cnt'];
            }
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
