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
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('OroCRMSalesBundle:Opportunity')
                        ->getFunnelChartData(
                            'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                            'grandTotal'
                        )
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }

    /**
     * @Route(
     *      "/sales_flow_b2c_streamline/chart/{widget}",
     *      name="orocrm_magento_dashboard_sales_flow_b2c_streamline_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:salesFlowChart.html.twig")
     */
    public function mySalesFlowB2CStreamlineAction($widget)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('OroCRMSalesBundle:Opportunity')
                        ->getStreamlineFunnelChartData(
                            'OroCRM\Bundle\MagentoBundle\Entity\Cart',
                            'grandTotal'
                        )
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }
}
