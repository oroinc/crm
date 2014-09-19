<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Dashboard;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ChartView;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/sales_flow_b2c/chart/{widget}",
     *      name="orocrm_channel_dashboard_average_customer_lifetime_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMChannelBundle:Dashboard:averageCustomerLifetime.html.twig")
     */
    public function averageCustomerLifetimeAction($widget)
    {
##################################################################
        // calculate slice date
        $currentYear  = (int)date('Y');
        $currentMonth = (int)date('m');

        $sliceYear  = $currentMonth == 12 ? $currentYear : $currentYear - 1;
        $sliceMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
        $sliceDate  = new \DateTime(sprintf('%s-%s-01', $sliceYear, $sliceMonth), new \DateTimeZone('UTC'));

        // calculate match for month and default channel template
        $monthMatch      = [];
        $channelTemplate = [];
        if ($sliceYear != $currentYear) {
            for ($i = $sliceMonth; $i <= 12; $i++) {
                $monthMatch[$i]                  = ['year' => $sliceYear, 'month' => $i];
                $channelTemplate[$sliceYear][$i] = 0;
            }
        }
        for ($i = 1; $i <= $currentMonth; $i++) {
            $monthMatch[$i]                    = ['year' => $currentYear, 'month' => $i];
            $channelTemplate[$currentYear][$i] = 0;
        }
##################################################################


##################################################################
        /** @var EntityManager $entityManager */
        $em        = $this->getDoctrine();
        $aclHelper = $this->get('oro_security.acl_helper');

        $queryBuilder = $em->getRepository('OroCRMChannelBundle:Channel')->createQueryBuilder('c');
        $queryBuilder->select('c.id, c.name')->orderBy('c.name');
        $channels = $aclHelper->apply($queryBuilder)->execute();

        // prepare result template
        $result = [];
        foreach ($channels as $channel) {
            $channelId          = $channel['id'];
            $channelName        = $channel['name'];
            $result[$channelId] = ['name' => $channelName, 'data' => $channelTemplate];
        }
##################################################################

##################################################################
        $amountStatistics = $em->getRepository('OroCRMChannelBundle:DatedLifetimeValue')->findAll();

##################################################################

##################################################################

        foreach ($amountStatistics as $datedLifetimeValue) {
            $channelId = (int)$datedLifetimeValue->getDataChannel()->getId();
            $month     = (int)$datedLifetimeValue->getMonth();
            $year      = $monthMatch[$month]['year'];
            $amount    = (float)$datedLifetimeValue->getAmount();

            if (isset($result[$channelId]['data'][$year][$month])) {
                $result[$channelId]['data'][$year][$month] += $amount;
            }
        }
        // prepare chart items
        $items = $this->prepareChartItems($result);
##################################################################

#########################################################################
        /** @var Translator $translator */
        $translator = $this->get('translator');

        /** @var ChartViewBuilder $viewBuilder */
        $viewBuilder = $this->container->get('oro_chart.view_builder');

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')
            ->getWidgetAttributesForTwig('average_customer_lifetime_chart');

        $widgetAttr['chartView'] = $this->getView($translator, $viewBuilder, $items);
#########################################################################

        return $widgetAttr;
    }

    /**
     * @param Translator       $translator
     * @param ChartViewBuilder $viewBuilder
     * @param array            $items
     *
     * @return ChartView
     */
    protected function getView(Translator $translator, ChartViewBuilder $viewBuilder, $items)
    {
        return $viewBuilder
            ->setOptions(
                [
                    'name'        => 'multiline_chart',
                    "data_schema" => [
                        "label" => ["field_name" => "month", "label" => null, "type" => "date"],
                        "value" => [
                            "field_name" => "amount",
                            "label"      => $translator->trans(
                                    'orocrm.channel.dashboard.average_customer_lifetime_chart.lifetime'
                                )
                        ],
                    ],
                ]
            )
            ->setArrayData($items)
            ->getView();
    }

    protected function prepareChartItems($resultTemplate)
    {
        $items = [];

        foreach ($resultTemplate as $row) {
            $channelName = $row['name'];
            $channelData = $row['data'];

            $items[$channelName] = [];

            foreach ($channelData as $year => $monthData) {
                foreach ($monthData as $month => $amount) {
                    $items[$channelName][] = [
                        'month'  => sprintf('%04d-%02d-01', $year, $month),
                        'amount' => $amount
                    ];
                }
            }
        }
        return $items;
    }
}
