<?php

namespace OroCRM\Bundle\CampaignBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    const CAMPAIGN_LEAD_COUNT = 5;
    const CAMPAIGN_OPPORTUNITY_COUNT = 5;
    const CAMPAIGN_CLOSE_REVENUE_COUNT = 5;

    /**
     * @Route(
     *      "/campaign_lead/chart/{widget}",
     *      name="orocrm_campaign_dashboard_campaigns_leads_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCRMCampaignBundle:Dashboard:campaignLeads.html.twig")
     */
    public function campaignLeadsAction($widget)
    {
        $items = $this->getDoctrine()
            ->getRepository('OroCRMCampaignBundle:Campaign')
            ->getCampaignsLeads(
                $this->get('oro_security.acl_helper'),
                self::CAMPAIGN_LEAD_COUNT,
                $this->get('oro_dashboard.widget_configs')
                    ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                array(
                    'name' => 'bar_chart',
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array('field_name' => 'number')
                    ),
                    'settings' => array('xNoTicks' => count($items)),
                )
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/campaign_opportunity/chart/{widget}",
     *      name="orocrm_campaign_dashboard_campaigns_opportunity_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCRMCampaignBundle:Dashboard:campaignOpportunity.html.twig")
     */
    public function campaignOpportunityAction($widget)
    {
        $items = $this->getDoctrine()
            ->getRepository('OroCRMCampaignBundle:Campaign')
            ->getCampaignsOpportunities(
                $this->get('oro_security.acl_helper'),
                self::CAMPAIGN_OPPORTUNITY_COUNT,
                $this->get('oro_dashboard.widget_configs')
                    ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                array(
                    'name' => 'bar_chart',
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array('field_name' => 'number')
                    ),
                    'settings' => array('xNoTicks' => count($items)),
                )
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/campaign_by_close_revenue/chart/{widget}",
     *      name="orocrm_campaign_dashboard_campaigns_by_close_revenue_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCRMCampaignBundle:Dashboard:campaignByCloseRevenue.html.twig")
     */
    public function campaignByCloseRevenueAction($widget)
    {
        $items = $this->getDoctrine()
            ->getRepository('OroCRMCampaignBundle:Campaign')
            ->getCampaignsByCloseRevenue(
                $this->get('oro_security.acl_helper'),
                self::CAMPAIGN_CLOSE_REVENUE_COUNT,
                $this->get('oro_dashboard.widget_configs')
                    ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                array(
                    'name' => 'bar_chart',
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array('field_name' => 'closeRevenue', 'formatter' => 'formatCurrency')
                    ),
                    'settings' => array('xNoTicks' => count($items)),
                )
            )
            ->getView();

        return $widgetAttr;
    }
}
