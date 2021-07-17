<?php

namespace Oro\Bundle\AnalyticsBundle\Builder;

use Oro\Bundle\ChannelBundle\Entity\Channel;

interface AnalyticsBuilderInterface
{
    /**
     * @param Channel $channel
     * @return bool
     */
    public function supports(Channel $channel);

    public function build(Channel $entity, array $ids = []);
}
