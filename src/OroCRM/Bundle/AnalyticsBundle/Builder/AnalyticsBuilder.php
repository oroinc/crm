<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;

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
     * @param AnalyticsAwareInterface $entity
     *
     * @return bool Build was performed
     */
    public function build(AnalyticsAwareInterface $entity)
    {
        $update = false;

        foreach ($this->builders as $builder) {
            if ($builder->supports($entity)) {
                $update = $update || $builder->build($entity);
            }
        }

        return $update;
    }
}
