<?php

namespace Oro\Bundle\AnalyticsBundle\EventListener;

use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;

class TimezoneChangeListener
{
    /** @var RFMMetricStateManager */
    protected $metricStateManager;

    /**
     * @var CalculateAnalyticsScheduler
     */
    protected $calculateAnalyticsScheduler;

    public function __construct(
        RFMMetricStateManager $metricStateManager,
        CalculateAnalyticsScheduler $calculateAnalyticsScheduler
    ) {
        $this->metricStateManager = $metricStateManager;
        $this->calculateAnalyticsScheduler = $calculateAnalyticsScheduler;
    }

    public function onConfigUpdate(ConfigUpdateEvent $event)
    {
        if (!$event->isChanged('oro_locale.timezone')) {
            return;
        }
        $this->metricStateManager->resetMetrics();
        $this->calculateAnalyticsScheduler->scheduleForAllChannels();
    }
}
