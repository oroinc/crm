<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/sales_flow_b2c/chart/{widget}",
     *      name="orocrm_magento_dashboard_sales_flow_b2c_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:salesFlowChart.html.twig")
     */
    public function mySalesFlowB2CAction($widget)
    {
        $dateTo = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateFrom = new \DateTime(
            $dateTo->format('Y') . '-01-' . ((ceil($dateTo->format('n') / 3) - 1) * 3 + 1),
            new \DateTimeZone('UTC')
        );

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getApplicableWorkflowByEntityClass(
            'OroCRM\Bundle\MagentoBundle\Entity\Cart'
        );

        /** @var CartRepository $shoppingCartRepository */
        $shoppingCartRepository = $this->getDoctrine()->getRepository('OroCRMMagentoBundle:Cart');

        $data = $shoppingCartRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflow,
            $this->get('oro_security.acl_helper')
        );

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions(
                array(
                    'name' => 'flow_chart',
                    'settings' => array('quarterDate' => $dateFrom),
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array('field_name' => 'value'),
                        'isNozzle' => array('field_name' => 'isNozzle'),
                    )
                )
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/average_order_amount_by_customer",
     *      name="orocrm_magento_dashboard_average_order_amount_by_customer",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMMagentoBundle:Dashboard:ordersByCustomers.html.twig")
     */
    public function averageOrderAmountByCustomerAction()
    {
        // calculate slice date
        $currentYear  = (int)date('Y');
        $currentMonth = (int)date('m');

        $sliceYear  = $currentMonth == 12 ? $currentYear : $currentYear - 1;
        $sliceMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $sliceDate  = new \DateTime(sprintf('%s-%s-01', $sliceYear, $sliceMonth), new \DateTimeZone('UTC'));

        // calculate match for month and default channel template
        $monthMatch = [];
        $channelTemplate = [];
        if ($sliceYear != $currentYear) {
            for ($i = $sliceMonth; $i <= 12; $i++) {
                $monthMatch[$i] = ['year' => $sliceYear, 'month' => $i];
                $channelTemplate[$sliceYear][$i] = 0;
            }
        }
        for ($i = 1; $i <= $currentMonth; $i++) {
            $monthMatch[$i] = ['year' => $currentYear, 'month' => $i];
            $channelTemplate[$currentYear][$i] = 0;
        }

        // get all channels
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManagerForClass('OroCRMChannelBundle:Channel');
        $aclHelper = $this->get('oro_security.acl_helper');
        $queryBuilder = $entityManager->getRepository('OroCRMChannelBundle:Channel')->createQueryBuilder('c');
        $queryBuilder->select('c.id, c.name')->orderBy('c.name');
        $channels = $aclHelper->apply($queryBuilder)->execute();

        // prepare result template
        $result = [];
        foreach ($channels as $channel) {
            $channelId = $channel['id'];
            $channelName = $channel['name'];
            $result[$channelId] = ['name' => $channelName, 'data' => $channelTemplate];
        }

        // execute query
        $sql = '
            SELECT data_channel_id, month_created, AVG(order_amount) as average_order_amount
            FROM (
                SELECT data_channel_id, EXTRACT(month from created_at) as month_created, COUNT(id) as order_amount
                FROM orocrm_magento_order
                WHERE created_at > DATE ?
                GROUP BY customer_id, data_channel_id, month_created
            ) as amount_query
            GROUP BY data_channel_id, month_created';


        $entityManager = $this->getDoctrine()->getManagerForClass('OroCRMMagentoBundle:Order');
        $amountStatistics = $entityManager->getConnection()->fetchAll($sql, array($sliceDate->format('Y-m-d')));

        foreach ($amountStatistics as $row) {
            $channelId   = (int)$row['data_channel_id'];
            $month       = (int)$row['month_created'];
            $year        = $monthMatch[$month]['year'];
            $orderAmount = (float)$row['average_order_amount'];

            if (isset($result[$channelId]['data'][$year][$month])) {
                $result[$channelId]['data'][$year][$month] += $orderAmount;
            }
        }

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

        $translator = $this->get('translator');

        $viewBuilder = $this->container->get('oro_chart.view_builder');
        $view = $viewBuilder
            ->setOptions(
                array(
                    'name' => 'multiline_chart',
                    "data_schema" => array(
                        "label" => array("field_name" => "month", "label" => null, "type" => "month"),
                        "value" => array(
                            "field_name" => "amount",
                            "label" => $translator->trans(
                                'orocrm.magento.dashboard.average_order_amount_by_customer_chart.order_amount'
                            )
                        ),
                    ),
                )
            )
            ->setArrayData($items)
            ->getView();

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')
            ->getWidgetAttributesForTwig('average_order_amount_by_customer_chart');
        $widgetAttr['chartView'] = $view;

        return $widgetAttr;
    }
}
