<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class AnalyticsBuilder
{
    /**
     * @var AnalyticsBuilderInterface[]
     */
    protected $builders = [];

    /**
     * @param AnalyticsBuilderInterface $analyticsBuilder
     */
    public function addBuilder(AnalyticsBuilderInterface $analyticsBuilder)
    {
        $this->builders[] = $analyticsBuilder;
    }

    /**
     * @return AnalyticsBuilderInterface[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }

    /**
     * @param Channel $channel
     * @param array $ids
     */
    public function build(Channel $channel, array $ids = [])
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($channel)) {
                $builder->build($channel, $ids);
            }
        }
    }
}
