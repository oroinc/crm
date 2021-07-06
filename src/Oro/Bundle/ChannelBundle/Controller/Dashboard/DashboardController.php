<?php

namespace Oro\Bundle\ChannelBundle\Controller\Dashboard;

use Oro\Bundle\ChannelBundle\Provider\Lifetime\AverageLifetimeWidgetProvider;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Adds action to work with average lifetime sales chart
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            WidgetConfigs::class,
            AverageLifetimeWidgetProvider::class,
            ChartViewBuilder::class,
            ConfigProvider::class,
        ]);
    }

    /**
     * @Route(
     *      "/chart/{widget}",
     *      name="oro_channel_dashboard_average_lifetime_sales_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("@OroChannel/Dashboard/averageLifetimeSales.html.twig")
     * @param Request $request
     * @param string $widget
     * @return array
     */
    public function averageLifetimeSalesAction(Request $request, $widget)
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $dateRange = $widgetConfigs
            ->getWidgetOptions($request->query->get('_widgetId', null))
            ->get('dateRange');

        $data = $this->get(AverageLifetimeWidgetProvider::class)->getChartData($dateRange);
        $widgetAttr = $widgetConfigs->getWidgetAttributesForTwig($widget);
        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->get(ConfigProvider::class)->getChartConfig('average_lifetime_sales')
        );

        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
            ->setArrayData($data)
            ->setOptions($chartOptions)
            ->getView();

        return $widgetAttr;
    }
}
