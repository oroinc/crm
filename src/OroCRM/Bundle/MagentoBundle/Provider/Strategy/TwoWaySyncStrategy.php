<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Strategy;

use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
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
     * @var DataConverterInterface
     */
    protected $dataConverter;

    /**
     * @param DataConverterInterface $dataConverter
     */
    public function __construct(DataConverterInterface $dataConverter)
    {
        $this->dataConverter = $dataConverter;
    }

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

        $oldValues = $this->getChangeSetValues($changeSet, 'old');
        $snapshot = array_merge($localData, $oldValues);
        $localChanges = array_keys($oldValues);
        $remoteChanges = array_keys(array_diff_assoc($remoteData, $snapshot));
        $conflicts = array_intersect($remoteChanges, $localChanges);

        if (!$conflicts) {
            return $remoteData;
        }

        foreach ($conflicts as $conflict) {
            if (!array_key_exists($conflict, $remoteData)) {
                continue;
            }

            if ($strategy === TwoWaySyncConnectorInterface::LOCAL_WINS) {
                $remoteData[$conflict] = $localData[$conflict];
            }
        }

        return $remoteData;
    }

    /**
     * @param array $changeSet
     * @param string $key
     * @return array
     */
    protected function getChangeSetValues($changeSet, $key)
    {
        $oldValues = array_map(
            function ($data) use ($key) {
                if (empty($data[$key])) {
                    return null;
                }

                return $data[$key];
            },
            $changeSet
        );

        return array_filter($this->dataConverter->convertToExportFormat($oldValues));
    }
}
