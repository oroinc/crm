<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param ManagerRegistry     $registry
     * @param TranslatorInterface $translator
     * @param AclHelper           $aclHelper
     */
    public function __construct(ManagerRegistry $registry, TranslatorInterface $translator, AclHelper $aclHelper)
    {
        $this->registry   = $registry;
        $this->translator = $translator;
        $this->aclHelper  = $aclHelper;
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

        $ulcTimezone = new \DateTimeZone('UTC');
        $now  = new \DateTime('now', $ulcTimezone);
        $past = clone $now;
        $past = $past->sub(new \DateInterval("P11M"));
        $past = \DateTime::createFromFormat('Y-m-d', $past->format('Y-m-01'), $ulcTimezone);

        $past->setTime(0, 0, 0);

        $datePeriod = new \DatePeriod($past, new \DateInterval('P1M'), $now);
        $dates      = [];
        $items      = [];

        // get all integration channels
        $channels   = $channelRepository->getByType($this->aclHelper, 'magento');
        $channelIds = array_keys($channels);
        $data       = $customerRepository->getGroupedByChannelArray($this->aclHelper, $past, $now, $channelIds);

        // create dates by date period
        /** @var \DateTime $dt */
        foreach ($datePeriod as $dt) {
            $key = $dt->format('Y-m');
            $dates[$key] = array(
                'month_year' => sprintf('%s-01', $key),
                'cnt'        => 0
            );
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

        $customerCountLabel = $this->translator->trans(
            'orocrm.magento.dashboard.new_magento_customers_chart.customer_count'
        );
        $chartOptions = [
            'name'        => 'multiline_chart',
            'data_schema' => [
                'label' => [
                    'field_name' => 'month_year',
                    'label'      => null,
                    'type'       => 'month'
                ],
                'value' => [
                    'field_name' => 'cnt',
                    'label'      => $customerCountLabel
                ],
            ],
        ];

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }
}
