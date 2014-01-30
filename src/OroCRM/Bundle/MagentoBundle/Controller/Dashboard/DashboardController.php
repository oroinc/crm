<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));

        return array_merge(
            [
                'quarterDate' =>  new \DateTime(
                    $currentDate->format('Y') . '-01-' . ((ceil($currentDate->format('n') / 3) - 1) * 3 + 1),
                    new \DateTimeZone('UTC')
                )
            ],
            $this->getDoctrine()
                ->getRepository('OroCRMMagentoBundle:Cart')
                ->getFunnelChartData(
                    [
                        'open',
                        'contacted'
                    ],
                    [
                        'abandoned',
                        'converted_to_opportunity',
                        'converted'
                    ]
                ),
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }
}
