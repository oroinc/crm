<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;
use Oro\Bundle\MagentoBundle\Provider\Iso2CodeProvider;

abstract class AbstractAddressDataConverter extends IntegrationAwareDataConverter
{
    /**
     * @var Iso2CodeProvider
     */
    protected $iso2CodeProvider;

    /**
     * @param Iso2CodeProvider $iso2CodeProvider
     */
    public function setIso2CodeProvider(Iso2CodeProvider $iso2CodeProvider)
    {
        $this->iso2CodeProvider = $iso2CodeProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'firstname' => 'firstName',
            'lastname' => 'lastName',
            'middlename' => 'middleName',
            'prefix' => 'namePrefix',
            'suffix' => 'nameSuffix',
            'region' => 'regionText',
            'region_id' => 'region:code', // Note, this is integer identifier of magento region
            'country_id' => 'country:iso2Code',
            'created_at' => 'created',
            'updated_at' => 'updated',
            'postcode' => 'postalCode',
            'telephone' => 'phone',
            'company' => 'organization',
            'city' => 'city',
            'street' => 'street',
            'street2' => 'street2'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (!empty($importedRecord['country_id'])) {
            $importedRecord['countryText'] = $importedRecord['country_id'];
        }
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);

        if (!empty($importedRecord['street'])) {
            $streets = $importedRecord['street'];
            if (is_string($streets) && strpos($streets, "\n") !== false) {
                list($importedRecord['street'], $importedRecord['street2']) = explode("\n", $importedRecord['street']);
            } elseif (is_array($streets)) {
                $importedRecord['street'] = reset($streets);
                $importedRecord['street2'] = next($streets);
            }
        }
        $importedRecord = $this->convertImportedRegion($importedRecord);
        $importedRecord = $this->convertImportedCountry($importedRecord);

        return $importedRecord;
    }

    /**
     * @param array $importedRecord
     * @return array
     */
    protected function convertImportedRegion(array $importedRecord)
    {
        if (empty($importedRecord['region']['code'])) {
            $importedRecord['region'] = null;
        }

        return $importedRecord;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $exportedRecord = parent::convertToExportFormat($exportedRecord, $skipNullValues);

        $streets = [];

        if (!empty($exportedRecord['street'])) {
            $streets[] = $exportedRecord['street'];
            unset($exportedRecord['street']);
        }

        if (!empty($exportedRecord['street2'])) {
            $streets[] = $exportedRecord['street2'];
            unset($exportedRecord['street2']);
        }

        if ($streets) {
            $exportedRecord['street'] = $streets;
        }

        return $exportedRecord;
    }

    /**
     * @param array $importedRecord
     * @return array
     */
    private function convertImportedCountry(array $importedRecord)
    {
        if ($this->iso2CodeProvider && !empty($importedRecord['country']['iso2Code'])) {
            $foundCode = $this
                ->iso2CodeProvider
                ->getIso2CodeByCountryId($importedRecord['country']['iso2Code']);
            if ($foundCode) {
                $importedRecord['country']['iso2Code'] = $foundCode;
            } else {
                $importedRecord['country'] = null;
            }
        }

        return $importedRecord;
    }
}
