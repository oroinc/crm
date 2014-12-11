<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
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
        $this->registry   = $registry;
        $this->aclHelper  = $aclHelper;
        $this->configProvider = $configProvider;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     *
     * @return ChartView
     */
    public function getNewCustomerChartView(ChartViewBuilder $viewBuilder)
    {
        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->registry->getRepository('OroCRMMagentoBundle:Customer');

        /** @var ChannelRepository $channelRepository */
        $channelRepository = $this->registry->getRepository('OroCRMChannelBundle:Channel');

        $utcTimezone = new \DateTimeZone('UTC');
        $now  = new \DateTime('now', $utcTimezone);
        $past = clone $now;
        $past = $past->sub(new \DateInterval("P11M"));
        $past = \DateTime::createFromFormat('Y-m-d', $past->format('Y-m-01'), $utcTimezone);

        $past->setTime(0, 0, 0);

        $datePeriod = new \DatePeriod($past, new \DateInterval('P1M'), $now);
        $dates      = [];
        $items      = [];

        // get all integration channels
        $channels   = $channelRepository->getAvailableChannelNames($this->aclHelper, 'magento');
        $channelIds = array_keys($channels);
        $data       = $customerRepository->getGroupedByChannelArray($this->aclHelper, $past, null, $channelIds);

        // create dates by date period
        /** @var \DateTime $dt */
        foreach ($datePeriod as $dt) {
            $key = $dt->format('Y-m');
            $dates[$key] = [
                'month_year' => sprintf('%s-01', $key),
                'cnt'        => 0
            ];
        }

        foreach ($data as $row) {
            $key         = date("Y-m", strtotime(sprintf('%s-%s', $row['yearCreated'], $row['monthCreated'])));
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

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }
}
