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
     * @param ManagerRegistry     $registry
     * @param TranslatorInterface $translator
     * @param AclHelper           $aclHelper
     */
    public function __construct(ManagerRegistry $registry, TranslatorInterface $translator)
    {
        $this->registry   = $registry;
        $this->translator = $translator;
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

        $now  = new \DateTime('now', new \DateTimeZone('UTC'));
        $past = clone $now;
        $past = $past->sub(new \DateInterval("P12M"));

        $now->setTime(23, 59, 59);
        $past->setTime(0, 0, 0);

        $datePeriod = new \DatePeriod($past, new \DateInterval('P1M'), $now);
        $dates      = [];
        $items      = [];

        // get all integration channels
        $channels   = $channelRepository->getByType('magento');
        $channelIds = array_keys($channels);
        $data       = $customerRepository->getGroupedByChannelArray($past, $now, $channelIds);

        // create dates by date period
        foreach ($datePeriod as $dt) {
            $key = $dt->format('Y-m');
            $dates[$key] = array(
                'month_year' => sprintf('%s-01', $key),
                'cnt'        => 0
            );
        }

        foreach ($data as $v) {
            $key         = $v['createdAt']->format('Y-m');
            $channelName = $channels[$v[1]]['name'];

            if (!isset($items[$channelName])) {
                $items[$channelName] = $dates;
            }
            $items[$channelName][$key]['cnt'] = (int)$v['cnt'];
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
