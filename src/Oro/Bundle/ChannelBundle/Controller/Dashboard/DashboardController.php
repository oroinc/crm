<?php

namespace Oro\Bundle\ChannelBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/dashboard")
 */
class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/chart/{widget}",
     *      name="oro_channel_dashboard_average_lifetime_sales_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroChannelBundle:Dashboard:averageLifetimeSales.html.twig")
     * @param Request $request
     * @param string $widget
     * @return array
     */
    public function averageLifetimeSalesAction(Request $request, $widget)
    {
        $dateRange = $this->get('oro_dashboard.widget_configs')
            ->getWidgetOptions($request->query->get('_widgetId', null))
            ->get('dateRange');
        $data = $this->get('oro_channel.provider.lifetime.average_widget_provider')->getChartData($dateRange);
        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->get('oro_chart.config_provider')->getChartConfig('average_lifetime_sales')
        );

        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions($chartOptions)
            ->getView();

        return $widgetAttr;
    }
}
