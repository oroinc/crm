<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Strategy;

interface TwoWaySyncStrategyInterface
{
    /**
     * @param array $changeSet
     * @param array $localData
     * @param array $remoteData
     * @param string $strategy
     *
     * @return array Result data
     */
    public function merge(array $changeSet, array $localData, array $remoteData, $strategy);
}
