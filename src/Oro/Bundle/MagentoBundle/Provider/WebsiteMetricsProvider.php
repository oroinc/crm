<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\TrackingCustomerIdentificationEvents as TCI;

/**
 * Calculates metrics based on tracking events.
 *
 * It uses predefined set of events and metrics.
 */
class WebsiteMetricsProvider
{
    /** @var TrackingVisitProviderInterface */
    protected $visitProvider;

    /** @var TrackingVisitEventProviderInterface */
    protected $visitEventProvider;

    /**
     * @param TrackingVisitProviderInterface $visitProvider
     * @param TrackingVisitEventProviderInterface $visitEventProvider
     */
    public function __construct(
        TrackingVisitProviderInterface $visitProvider,
        TrackingVisitEventProviderInterface $visitEventProvider
    ) {
        $this->visitProvider = $visitProvider;
        $this->visitEventProvider = $visitEventProvider;
    }

    /**
     * @param Customer[] $customers
     *
     * @return array
     */
    public function getTemplateData(array $customers)
    {
        return [
            'metrics' => $this->getMetrics($customers),
        ];
    }

    /**
     * @return string[]
     */
    protected function getEvents()
    {
        return [
            TCI::EVENT_VISIT,
            TCI::EVENT_CART_ITEM_ADDED,
            TCI::EVENT_CHECKOUT_STARTED,
            TCI::EVENT_CUSTOMER_LOGIN,
        ];
    }

    /**
     *
     * @param Customer[] $customers Filter by customers
     *
     * @return array
     */
    protected function getMetrics(array $customers)
    {
        $eventMetrics = $this->getEventMetrics($customers, $this->getEvents());

        $pageViewsCount = $eventMetrics[TCI::EVENT_VISIT]['count'];
        $itemsAddedCount = $eventMetrics[TCI::EVENT_CART_ITEM_ADDED]['count'];
        $lastItem = $eventMetrics[TCI::EVENT_CART_ITEM_ADDED]['last'];
        $checkoutsCount = $eventMetrics[TCI::EVENT_CHECKOUT_STARTED]['count'];
        $lastCheckout = $eventMetrics[TCI::EVENT_CHECKOUT_STARTED]['last'];
        $lastLogin = $eventMetrics[TCI::EVENT_CUSTOMER_LOGIN]['last'];

        $lastViewedPage = $this->visitEventProvider->getLastViewedPage($customers);
        $mostVisitedPage = $this->visitEventProvider->getMostViewedPage($customers);

        $visitMetrics = $this->visitProvider->getAggregates($customers);
        $visitsCount = $visitMetrics['count'];

        $metrics = [
            'page_view_count'         => $pageViewsCount,
            'checkout_count'          => $checkoutsCount,
            'item_added_count'        => $itemsAddedCount,
            'visit_count'             => $visitsCount,
            'average_visit_views'     => $visitsCount ? $pageViewsCount / $visitsCount : 0,
            'average_visit_items'     => $visitsCount ? $itemsAddedCount / $visitsCount : 0,
            'average_visit_checkouts' => $checkoutsCount ? $visitsCount / $checkoutsCount : 0,
            'average_visit_monthly'   => $visitMetrics['monthly'],
            'most_viewed_page'        => $mostVisitedPage,
            'last_viewed_page'        => $lastViewedPage,
            'last_login'              => $lastLogin,
            'last_item'               => $lastItem,
            'last_checkout'           => $lastCheckout,
            'last_visit'              => $visitMetrics['last'],
        ];

        return $metrics;
    }

    /**
     * Get event count and last date of occurrence grouped by event name
     *
     * @param Customer[] $customers Filter by customers
     * @param string[] $eventNames Filter by event names
     *
     * @return array
     */
    protected function getEventMetrics(array $customers, array $eventNames)
    {
        $defaults = [];
        foreach ($eventNames as $eventName) {
            $defaults[$eventName] = ['count' => 0, 'last' => null];
        }

        $metrics = $this->visitEventProvider
            ->getCustomerEventAggregates($customers, $eventNames);

        return $metrics + $defaults;
    }
}
