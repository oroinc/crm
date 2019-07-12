<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\ChartBundle\Utils\ColorUtils;
use Oro\Bundle\MagentoBundle\Provider\TrackingCustomerIdentificationEvents as TCI;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provide chart view instance with tracking events data sorted by date.
 *
 * Set chart colors config based on number of shade colors provided by subclasses
 * and group the data by a field provided also by subclasses
 * The set of events is predefined.
 * Subclasses are responsible to provide the data.
 */
abstract class WebsiteChartProvider
{
    /**
     * @var float COLOR_SHADE_PERCENT Percentage of shade to apply to colors
     */
    const COLOR_SHADE_PERCENT = 0.2;

    /**
     * @var array Map of events to legend labels
     */
    public static $legendLabelsMap = [
        TCI::EVENT_CART_ITEM_ADDED => 'oro.magento.website_activity.chart.legend.event_cart_item_added',
        TCI::EVENT_CHECKOUT_STARTED => 'oro.magento.website_activity.chart.legend.event_checkout_started',
        TCI::EVENT_VISIT => 'oro.magento.website_activity.chart.legend.event_visit',
    ];

    /** @var TrackingVisitEventProviderInterface */
    protected $visitEventProvider;

    /** @var ConfigProvider */
    protected $configProvider;

    /** @var ChartViewBuilder */
    protected $chartViewBuilder;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TrackingVisitEventProviderInterface $visitEventProvider
     * @param ConfigProvider $configProvider
     * @param ChartViewBuilder $chartViewBuilder
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TrackingVisitEventProviderInterface $visitEventProvider,
        ConfigProvider $configProvider,
        ChartViewBuilder $chartViewBuilder,
        TranslatorInterface $translator
    ) {
        $this->visitEventProvider = $visitEventProvider;
        $this->configProvider = $configProvider;
        $this->chartViewBuilder = $chartViewBuilder;
        $this->translator = $translator;
    }

    /**
     * @param Customer[] $customers
     *
     * @return array
     */
    public function getTemplateData(array $customers)
    {
        return [
            'chartView' => $this->getChartView($customers),
        ];
    }

    /**
     * Get array of events to plot on graph
     *
     * @param Customer[] $customers Filter by customers
     *
     * @return ChartView
     */
    public function getChartView(array $customers)
    {
        $data = $this->getData($customers);
        $numberOfShadedColors = $this->getNumberOfShadeColors($data);

        return $this->chartViewBuilder
            ->setArrayData($this->transformData($data))
            ->setOptions($this->getConfig($numberOfShadedColors))
            ->getView();
    }

    /**
     * @param array $data
     *
     * @return int
     */
    abstract protected function getNumberOfShadeColors($data);

    /**
     * @param array $row
     *
     * @return string
     */
    abstract protected function formatGroup(array $row);

    /**
     * @param Customer[] $customers Filter by customers
     *
     * @return array
     */
    abstract protected function getData(array $customers);

    /**
     * @return string[]
     */
    protected function getEvents()
    {
        return [
            TCI::EVENT_CART_ITEM_ADDED,
            TCI::EVENT_CHECKOUT_STARTED,
            TCI::EVENT_VISIT,
        ];
    }

    /**
     * Get translated legend data group name from the event name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getLegendLabel($name)
    {
        return $this->translator->trans(isset(self::$legendLabelsMap[$name]) ? self::$legendLabelsMap[$name] : $name);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function transformData(array $data)
    {
        $groups = [];
        $emptyData = [];

        $dates = array_unique(array_column($data, 'date'));
        sort($dates);
        $dates = array_flip(array_values($dates));

        foreach ($dates as $date => $index) {
            $emptyData[] = [
                'count' => 0,
                'date' => $date,
            ];
        }

        foreach ($data as $row) {
            $group = $this->formatGroup($row);

            if (!isset($groups[$group])) {
                $groups[$group] = $emptyData;
            }

            $index = $dates[$row['date']];
            $groups[$group][$index] = [
                'count' => (int) $row['cnt'],
                'date' => $row['date'],
            ];
        }

        return $groups;
    }

    /**
     * Get chart config with instered color shades
     *
     * @param int $numberOfShadedColors Number of shaded colors
     *
     * @return array
     */
    protected function getConfig($numberOfShadedColors)
    {
        $barConfig = $this->configProvider->getChartConfig('stackedbar_chart');

        $colors = ColorUtils::insertShadeColors(
            $barConfig['default_settings']['chartColors'],
            $numberOfShadedColors,
            static::COLOR_SHADE_PERCENT
        );

        $config = array_merge_recursive(
            [
                'name' => 'stackedbar_chart',
                'settings' => [
                    'chartColors' => $colors,
                ]
            ],
            $this->configProvider->getChartConfig('website_chart')
        );

        return $config;
    }
}
