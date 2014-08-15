<?php

namespace OroCRM\Bundle\CampaignBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

/**
 * @Route("/campaign/event")
 */
class CampaignEventController extends Controller
{
    /**
     * @param string $period
     * @param string $campaignCode
     * @return array
     *
     * @Route("/plot/{period}/{campaignCode}", name="orocrm_campaign_event_plot")
     * @AclAncestor("orocrm_campaign_view")
     * @Template
     */
    public function plotAction($period, $campaignCode)
    {
        $supportedPeriods = [
            Campaign::PERIOD_HOURLY,
            Campaign::PERIOD_DAILY,
            Campaign::PERIOD_MONTHLY,
        ];
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

        $gridParameters = [
            'code'                           => $campaignCode,
            PagerInterface::PAGER_ROOT_PARAM => [
                PagerInterface::DISABLED_PARAM => true
            ]
        ];

        $datagrid = $this
            ->get('oro_datagrid.datagrid.manager')
            ->getDatagridByRequestParams(
                $gridName,
                $gridParameters
            );

        $chartName = 'campaign_line_chart';
        $chartView = $this
            ->get('oro_chart.view_builder')
            ->setDataGrid($datagrid)
            ->setOptions(
                array_merge_recursive(
                    [
                        'name'             => $chartName,
                        'default_settings' => [
                            'period' => $period
                        ]
                    ],
                    $this
                        ->get('oro_chart.config_provider')
                        ->getChartConfig($chartName)
                )
            )
            ->getView();

        return [
            'chartView' => $chartView
        ];
    }
}
