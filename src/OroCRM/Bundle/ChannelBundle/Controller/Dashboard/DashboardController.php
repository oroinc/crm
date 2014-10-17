<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/dashboard")
 */
class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/chart/{widget}",
     *      name="orocrm_channel_dashboard_average_lifetime_sales_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMChannelBundle:Dashboard:averageLifetimeSales.html.twig")
     */
    public function averageLifetimeSalesAction($widget)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $label      = $translator->trans('orocrm.channel.dashboard.average_lifetime_sales_chart.axis_label');
        $data       = $this->get('orocrm_channel.provider.lifetime.average_widget_provider')->getChartData();

        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions(
                [
                    'name'        => 'multiline_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'month_year', 'label' => null, 'type' => 'month'],
                        'value' => ['field_name' => 'amount', 'label' => $label, 'type' => 'currency'],
                    ],
                ]
            )
            ->getView();

        return $widgetAttr;
    }
}
