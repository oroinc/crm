<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

abstract class AbstractAddressDataConverter extends IntegrationAwareDataConverter
{
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
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        if (!empty($importedRecord['street']) && strpos($importedRecord['street'], "\n") !== false) {
            list($importedRecord['street'], $importedRecord['street2']) = explode("\n", $importedRecord['street']);
        }
        $importedRecord = $this->convertImportedRegion($importedRecord);

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
            $exportedRecord['street'] = implode("\n", $streets);
        }

        unset($exportedRecord['customer_id']);

        return $exportedRecord;
    }
}
