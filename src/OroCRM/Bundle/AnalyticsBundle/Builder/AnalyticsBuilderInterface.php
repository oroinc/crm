<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

interface AnalyticsBuilderInterface
{
    /**
     * @param Channel $channel
     * @return bool
     */
    public function supports(Channel $channel);

    /**
     * @param Channel $entity
     * @param array $ids
     */
    public function build(Channel $entity, array $ids = []);
}
