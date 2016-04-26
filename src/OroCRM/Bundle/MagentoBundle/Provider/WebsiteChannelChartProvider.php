<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Utils\ColorUtils;

use OroCRM\Bundle\MagentoBundle\Provider\TrackingCustomerIdentification as TCI;

/**
 * {@inheritdoc}
 */
class WebsiteChannelChartProvider extends WebsiteChartProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getNumberOfShadeColors($data)
    {
        $channels = array_unique(array_column($data, 'channel'));

        return count($channels) - 1;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatGroup(array $row)
    {
        return $row['channel'] . ' - ' . $row['name'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(array $customers)
    {
        return $this->visitEventProvider->getCustomerEventsCountByDateAndChannel(
            $customers,
            $this->getEvents()
        );
    }
}
