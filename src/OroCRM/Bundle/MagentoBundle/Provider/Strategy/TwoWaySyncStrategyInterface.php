<?php

namespace Oro\Bundle\MagentoBundle\Provider\Strategy;

use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

interface TwoWaySyncStrategyInterface extends StepExecutionAwareInterface
{
    /**
     * @param array $changeSet
     * @param array $localData
     * @param array $remoteData
     * @param string $strategy
     * @param array $additionalFields
     *
     * @return array Result data
     */
    public function merge(
        array $changeSet,
        array $localData,
        array $remoteData,
        $strategy,
        array $additionalFields = []
    );
}
