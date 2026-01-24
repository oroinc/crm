<?php

namespace Oro\Bundle\AnalyticsBundle\Builder;

use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * Defines the contract for analytics builders that compute and persist RFM metrics for customer entities.
 */
interface AnalyticsBuilderInterface
{
    /**
     * @param Channel $channel
     * @return bool
     */
    public function supports(Channel $channel);

    public function build(Channel $entity, array $ids = []);
}
