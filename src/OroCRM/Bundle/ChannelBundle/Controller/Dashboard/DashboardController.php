<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Dashboard;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\ChannelBundle\Entity\DatedLifetimeValue;

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
        /** @var ObjectManager $entityManager */
        $om = $this->getDoctrine()->getManager();

        /** @var AclHelper $aclHelper */
        $aclHelper = $this->get('oro_security.acl_helper');

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

        $channels         = $this->getChannels($om, $aclHelper);
        $resultTemplate   = $this->prepareResultTemplate($channels, $channelTemplate);
        $amountStatistics = $om->getRepository('OroCRMChannelBundle:DatedLifetimeValue')
            ->findAmountStatisticsByDate($sliceDate);

        foreach ($amountStatistics as $datedLifetimeValue) {
            /** @var DatedLifetimeValue $datedLifetimeValue */
            $channelId = (int)$datedLifetimeValue->getDataChannel()->getId();
            $month     = (int)$datedLifetimeValue->getMonth();
            $year      = $monthMatch[$month]['year'];
            $amount    = (float)$datedLifetimeValue->getAmount();

            if (isset($resultTemplate[$channelId]['data'][$year][$month])) {
                $resultTemplate[$channelId]['data'][$year][$month] += $amount;
            }
        }

        $items = $this->prepareChartItems($resultTemplate);

        /** @var Translator $translator */
        $translator = $this->get('translator');

        /** @var ChartViewBuilder $viewBuilder */
        $viewBuilder = $this->container->get('oro_chart.view_builder');
        $widgetAttr  = $this->get('oro_dashboard.widget_attributes')
            ->getWidgetAttributesForTwig('average_customer_lifetime_chart');

        $widgetAttr['chartView'] = $this->getView($translator, $viewBuilder, $items);

        return $widgetAttr;
    }


    /**
     * @param ObjectManager $om
     * @param AclHelper     $aclHelper
     *
     * @return mixed
     */
    protected function getChannels(ObjectManager $om, AclHelper $aclHelper)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $om->getRepository('OroCRMChannelBundle:Channel')->createQueryBuilder('c');
        $queryBuilder->select('c.id, c.name')->orderBy('c.name');
        return $aclHelper->apply($queryBuilder)->execute();
    }

    /**
     * @param array $channels
     * @param array $channelTemplate
     *
     * @return array
     */
    protected function prepareResultTemplate(array $channels, array $channelTemplate)
    {
        $result = [];
        foreach ($channels as $channel) {
            $channelId          = $channel['id'];
            $channelName        = $channel['name'];
            $result[$channelId] = ['name' => $channelName, 'data' => $channelTemplate];
        }

        return $result;
    }

    /**
     * @param $resultTemplate
     *
     * @return array
     */
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
                    'data_schema' => [
                        'label' => ['field_name' => 'month', 'label' => null, 'type' => 'date'],
                        'value' => [
                            'field_name' => 'amount',
                            'label'      => $this->getTranslationLabel($translator)
                        ],
                    ],
                ]
            )
            ->setArrayData($items)
            ->getView();
    }

    /**
     * @param Translator $translator
     *
     * @return string
     */
    protected function getTranslationLabel(Translator $translator)
    {
        return $translator->trans('orocrm.channel.dashboard.average_customer_lifetime_chart.lifetime');
    }
}
