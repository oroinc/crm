<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/opportunity_stage/{widget}",
     *      name="orocrm_sales_dashboard_opportunity_by_stage",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:opportunityByStage.html.twig")
     */
    public function opportunityByStageAction($widget)
    {
        return array_merge(
            [
                'items' => $this->getDoctrine()
                        ->getRepository('OroCRMSalesBundle:Opportunity')
                        ->getOpportunitiesByStage($this->get('oro_security.acl_helper'))
            ],
            $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
        );
    }
}
