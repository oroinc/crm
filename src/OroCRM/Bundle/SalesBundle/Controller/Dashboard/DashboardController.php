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
     * @Template("OroDashboardBundle:Dashboard:pieChart.html.twig")
     */
    public function myCalendarAction($widget)
    {
        $data = $this->getDoctrine()
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getOpportunitiesByLeadIndustry();

        $result = array_merge(
            [
                'data' => $data
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );

        return $result;
    }

    /**
     * @Route(
     *      "/opportunity_state/chart/{widget}",
     *      name="orocrm_sales_dashboard_opportunity_by_state_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:opportunityByState.html.twig")
     */
    public function opportunityByStateAction($widget)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('OroCRMSalesBundle:Opportunity')
                        ->getOpportunitiesByState($this->get('oro_security.acl_helper'))
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }
}
