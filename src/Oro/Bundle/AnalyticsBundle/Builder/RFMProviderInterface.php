<?php

namespace Oro\Bundle\AnalyticsBundle\Builder;

use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * Defines the contract for RFM (Recency, Frequency, Monetary) value providers,
 * enabling different channel types to supply customer analytics metrics.
 */
interface RFMProviderInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param Channel $channel
     * @return bool
     */
    public function supports(Channel $channel);

    /**
     * @param Channel $channel
     * @param array $ids
     * @return array
     */
    public function getValues(Channel $channel, array $ids = []);
}
