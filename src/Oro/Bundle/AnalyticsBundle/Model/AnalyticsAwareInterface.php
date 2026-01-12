<?php

namespace Oro\Bundle\AnalyticsBundle\Model;

use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * Marks entities as analytics-aware, indicating they support RFM metric tracking and customer analytics features.
 */
interface AnalyticsAwareInterface extends ChannelAwareInterface
{
}
