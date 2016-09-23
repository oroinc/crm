<?php

namespace Oro\Bundle\MagentoBundle\Provider;

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
        return $row['channel'] . ' - ' . $this->getLegendLabel($row['name']);
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
