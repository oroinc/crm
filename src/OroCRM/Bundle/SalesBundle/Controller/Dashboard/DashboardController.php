<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/opportunities_by_lead_source/chart/{widget}",
     *      name="oro_sales_dashboard_opportunities_by_lead_source_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:opportunitiesByLeadSourceChart.html.twig")
     */
    public function myCalendarAction($widget)
    {
        $items = $this->getDoctrine()
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getOpportunitiesByLeadIndustry();

        $result = array_merge(
            [
                'items' => $items
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );

        return $result;
    }
}
