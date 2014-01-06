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
    public function opportunitiesByLeadSourceAction($widget)
    {
        $data = $this->getDoctrine()
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getOpportunitiesByLeadSource($this->get('oro_security.acl_helper'));

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

    /**
     * @Route(
     *      "/{widget}/{activeTab}/{contentType}",
     *      name="orocrm_sales_dashboard_my_salesflow_chart",
     *      requirements={"widget"="[\w_-]+", "activeTab"="B2B|B2C", "contentType"="full|tab"},
     *      defaults={"activeTab" = "B2B", "contentType" = "full"}
     * )
     */
    public function salesFlowAction($widget, $activeTab, $contentType)
    {
        $loggedUserId     = $this->getUser()->getId();
        $renderMethod     = ($contentType === 'tab') ? 'render' : 'renderView';
        $activeTabContent = $this->$renderMethod(
            'OroCRMSalesBundle:Dashboard:salesflowChart.html.twig',
            array_merge(
                [
                    'items' => $activeTab == 'B2B'
                            ? $this->getDoctrine()
                                ->getRepository('OroCRMSalesBundle:Opportunity')
                                ->getOpportunitiesByLeads($this->get('oro_security.acl_helper'))
                            : $this->getDoctrine()
                                ->getRepository('OroCRMMagentoBundle:Cart')
                                ->getMagentoCartsByStates(),
                ],
                $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
            )
        );

        if ($contentType === 'tab') {
            return $activeTabContent;
        } else {
            $params = array_merge(
                [
                    'loggedUserId'     => $loggedUserId,
                    'activeTab'        => $activeTab,
                    'activeTabContent' => $activeTabContent
                ],
                $this->get('oro_dashboard.manager')->getWidgetAttributesForTwig($widget)
            );

            return $this->render(
                'OroCRMSalesBundle:Dashboard:salesflow.html.twig',
                $params
            );
        }
    }
}
