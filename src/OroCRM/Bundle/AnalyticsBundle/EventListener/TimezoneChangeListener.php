<?php

namespace Oro\Bundle\AnalyticsBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;

class TimezoneChangeListener
{
    /** @var RFMMetricStateManager */
    protected $metricStateManager;

    /**
     * @param RFMMetricStateManager $metricStateManager
     */
    public function __construct(RFMMetricStateManager $metricStateManager)
    {
        $this->metricStateManager = $metricStateManager;
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
        $this->metricStateManager->scheduleRecalculation();
    }
}
