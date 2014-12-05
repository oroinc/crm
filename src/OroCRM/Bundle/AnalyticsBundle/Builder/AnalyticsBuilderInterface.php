<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;

interface AnalyticsBuilderInterface
{
    /**
     * @param object $entity
     *
     * @return bool
     */
    public function supports($entity);

    /**
     * @param AnalyticsAwareInterface $entity
     *
     * @return bool
     */
    public function build(AnalyticsAwareInterface $entity);
}
