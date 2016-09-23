<?php

namespace Oro\Bundle\MagentoBundle\Provider;

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
        return $this->getLegendLabel($row['name']);
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
