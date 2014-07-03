<?php

namespace OroCRM\Bundle\CampaignBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/campaign/event")
 */
class CampaignEventController extends Controller
{
    /**
     * @Route("/plot/{period}/{campaignCode}", name="orocrm_campaign_event_plot")
     * @AclAncestor("orocrm_campaign_view")
     * @Template
     */
    public function plotAction($period, $campaignCode)
    {
        $supportedPeriods = array('hourly', 'daily', 'monthly');
        if (!in_array($period, $supportedPeriods)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Supported periods are: %s. %s given',
                    implode(', ', $supportedPeriods),
                    $period
                )
            );
        }

        $gridName = sprintf('campaign-tracking-detailed-report-%s-grid', $period);
        $gridParameters = array('code'=> $campaignCode);

        // todo: Load chart options correctly
        /*
        $datagrid = $this->get('oro_datagrid.datagrid.manager')->getDatagridByRequestParams(
            $gridName,
            $gridParameters
        );
        $chartOptions = array();
        $chartView = $this->get('oro_chart.view_builder')
            ->setDataGrid($datagrid)
            ->setOptions($chartOptions)
            ->getView();
        */

        return array(
            //'chartView' => $chartView,
            'gridName' => $gridName,
            'gridParameters' => $gridParameters
        );
    }
}
