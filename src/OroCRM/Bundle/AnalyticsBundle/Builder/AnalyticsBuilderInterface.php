<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

interface AnalyticsBuilderInterface
{
    /**
     * @param Channel $entity
     * @return bool
     */
    public function supports(Channel $entity);

    /**
     * @param Channel $entity
     * @param array $ids
     * @return mixed
     */
    public function build(Channel $entity, array $ids = []);
}
