<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $translator
     * @param AclHelper $aclHelper
     */
    public function __construct(ManagerRegistry $registry, TranslatorInterface $translator, AclHelper $aclHelper)
    {
        $this->registry = $registry;
        $this->translator = $translator;
        $this->aclHelper = $aclHelper;
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

        $orderAmountLabel = $this->translator->trans(
            'orocrm.magento.dashboard.average_order_amount_chart.order_amount'
        );
        $monthLabel = $this->translator->trans('orocrm.magento.dashboard.average_order_amount_chart.month');
        $chartOptions = [
            'name' => 'multiline_chart',
            'data_schema' => [
                'label' => [
                    'field_name' => 'month',
                    'label' => $monthLabel,
                    'type' => 'month'
                ],
                'value' => [
                    'field_name' => 'amount',
                    'label' => $orderAmountLabel,
                    'type' => 'currency'
                ],
            ],
        ];

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }
}
