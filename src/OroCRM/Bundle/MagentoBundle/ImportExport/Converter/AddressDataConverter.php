<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class AddressDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'firstname'  => 'firstName',
            'lastname'   => 'lastName',
            'middlename' => 'middleName',
            'prefix'     => 'namePrefix',
            'suffix'     => 'nameSuffix',
            'region'     => 'regionText',
            'region_id'  => 'region:code',
            'country_id' => 'country:iso2Code',
            'created_at' => 'created',
            'updated_at' => 'updated',
            'postcode'   => 'postalCode',
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
        // will be implemented for bidirectional sync
        throw new \Exception('Normalization is not implemented!');
    }
}
