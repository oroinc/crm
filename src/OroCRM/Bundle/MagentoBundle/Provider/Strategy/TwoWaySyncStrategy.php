<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Strategy;

use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class TwoWaySyncStrategy
{
    /**
     * @var array
     */
    protected $supportedStrategies = [
        TwoWaySyncConnectorInterface::REMOTE_WINS,
        TwoWaySyncConnectorInterface::LOCAL_WINS
    ];

    /**
     * @param array $changeSet
     * @param array $localData
     * @param array $remoteData
     * @param string $strategy
     *
     * @return array Result data
     */
    public function merge(
        array $changeSet,
        array $localData,
        array $remoteData,
        $strategy = TwoWaySyncConnectorInterface::REMOTE_WINS
    ) {
        if (!in_array($strategy, $this->supportedStrategies, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Strategy "%s" is not supported, expected one of "%s"',
                    $strategy,
                    implode(',', $this->supportedStrategies)
                )
            );
        }

        if (!$changeSet) {
            return $remoteData;
        }

        $oldValues = array_map(
            function ($data) {
                if (empty($data['old'])) {
                    return false;
                }

                return $data['old'];
            },
            $changeSet
        );

        $snapshot = array_merge($localData, $oldValues);
        $localChanges = array_keys($changeSet);
        $remoteChanges = array_keys(array_diff_assoc($remoteData, $snapshot));
        $conflicts = array_intersect($remoteChanges, $localChanges);

        if (!$conflicts) {
           return $remoteData;
        }

        foreach ($conflicts as $conflict) {
            if ($strategy === TwoWaySyncConnectorInterface::LOCAL_WINS) {
                $remoteData[$conflict] = $localData[$conflict];
            }
        }

        return $remoteData;
    }
}
