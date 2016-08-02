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
class WebsiteEventsChartProvider extends WebsiteChartProvider
{
    /**
     * {@inheritdoc}
     */
    protected function getNumberOfShadeColors($data)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function formatGroup(array $row)
    {
        return $row['name'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(array $customers)
    {
        return $this->visitEventProvider->getCustomerEventsCountByDate(
            $customers,
            $this->getEvents()
        );
    }
}
