<?php

namespace Oro\Bundle\AnalyticsBundle\Builder;

use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * Delegates the building of analytics to child builders.
 */
class AnalyticsBuilder
{
    /** @var iterable|AnalyticsBuilderInterface[] */
    private $builders;

    /**
     * @param iterable|AnalyticsBuilderInterface[] $builders
     */
    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    public function build(Channel $channel, array $ids = [])
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($channel)) {
                $builder->build($channel, $ids);
            }
        }
    }
}
