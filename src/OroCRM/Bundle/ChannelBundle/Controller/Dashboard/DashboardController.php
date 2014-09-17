<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\EntityManager;

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

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManagerForClass('OroCRMChannelBundle:Channel');
        $aclHelper = $this->get('oro_security.acl_helper');

        $queryBuilder = $entityManager->getRepository('OroCRMChannelBundle:Channel')->createQueryBuilder('c');
        $queryBuilder->select('c.id, c.name')->orderBy('c.name');
        $channels = $aclHelper->apply($queryBuilder)->execute();

        // prepare result template
        $result = [];
        foreach ($channels as $channel) {
            $channelId = $channel['id'];
            $channelName = $channel['name'];
            $result[$channelId] = ['name' => $channelName, 'data' => $channelTemplate];
        }


#########################################################################
        $translator  = $this->get('translator');
        $viewBuilder = $this->container->get('oro_chart.view_builder');
        $view        = $viewBuilder
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
            ->setArrayData([])
            ->getView();

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')
            ->getWidgetAttributesForTwig('average_customer_lifetime_chart');

        $widgetAttr['chartView'] = $view;
#########################################################################

        return $widgetAttr;
    }
}
