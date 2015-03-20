<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Strategy;

use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

class TwoWaySyncStrategy implements TwoWaySyncStrategyInterface
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
     * {@inheritdoc}
     */
    public function merge(
        array $changeSet,
        array $localData,
        array $remoteData,
        $strategy = TwoWaySyncConnectorInterface::REMOTE_WINS,
        array $additionalFields = []
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

        $importRemoteData = $this->dataConverter->convertToImportFormat($remoteData);
        $remoteData = $this->dataConverter->convertToExportFormat($importRemoteData);

        $oldValues = $this->getChangeSetValues($changeSet, 'old');
        $newValues = $this->getChangeSetValues($changeSet, 'new');
        foreach (array_keys($newValues) as $key) {
            if (!array_key_exists($key, $oldValues)) {
                $oldValues[$key] = null;
            }
        }

        $snapshot = array_merge($localData, $oldValues);
        $localChanges = array_keys($oldValues);

        $remoteChanges = [];
        foreach ($remoteData as $key => $remoteDataItem) {
            if (!array_key_exists($key, $snapshot)) {
                $remoteChanges[] = $key;

                continue;
            }
            if ($snapshot[$key] != $remoteDataItem) {
                $remoteChanges[] = $key;

                continue;
            }
        }

        $conflicts = array_intersect($remoteChanges, $localChanges);

        if (!$conflicts) {
            $conflicts = [];
        }

        foreach (array_merge($conflicts, $additionalFields) as $conflict) {
            if (!array_key_exists($conflict, $remoteData)) {
                continue;
            }

            if (!array_key_exists($conflict, $localData)) {
                continue;
            }

            if ($strategy === TwoWaySyncConnectorInterface::LOCAL_WINS) {
                $remoteData[$conflict] = $localData[$conflict];
            }
        }

        $localDataForUpdate = array_diff($localChanges, $conflicts);
        foreach ($localDataForUpdate as $property) {
            $remoteData[$property] = $localData[$property];
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
        $values = array_map(
            function ($data) use ($key) {
                if (empty($data[$key])) {
                    return null;
                }

                return $data[$key];
            },
            $changeSet
        );

        return array_filter($this->dataConverter->convertToExportFormat($values));
    }
}
