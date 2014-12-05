<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;

interface RFMProviderInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function supports($entity);

    /**
     * @param RFMAwareInterface $entity
     *
     * @return int
     */
    public function getValue(RFMAwareInterface $entity);
}
