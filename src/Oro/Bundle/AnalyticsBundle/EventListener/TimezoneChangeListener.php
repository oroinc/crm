<?php

namespace Oro\Bundle\AnalyticsBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;

class TimezoneChangeListener
{
    /** @var RFMMetricStateManager */
    protected $metricStateManager;

    /**
     * @var CalculateAnalyticsScheduler
     */
    protected $calculateAnalyticsScheduler;

    /**
     * @param RFMMetricStateManager $metricStateManager
     * @param CalculateAnalyticsScheduler $calculateAnalyticsScheduler
     */
    public function __construct(
        RFMMetricStateManager $metricStateManager,
        CalculateAnalyticsScheduler $calculateAnalyticsScheduler
    ) {
        $this->metricStateManager = $metricStateManager;
        $this->calculateAnalyticsScheduler = $calculateAnalyticsScheduler;
    }

    /**
     * @param ConfigUpdateEvent $event
     */
    public function onConfigUpdate(ConfigUpdateEvent $event)
    {
        if (!$event->isChanged('oro_locale.timezone')) {
            return;
        }
        $this->metricStateManager->resetMetrics();
        $this->calculateAnalyticsScheduler->scheduleForAllChannels();
    }
}
