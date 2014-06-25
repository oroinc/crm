<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class AddressDataConverter extends AbstractTableDataConverter
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
    protected function getBackendHeader()
    {
        // will be implemented for bidirectional sync
        throw new \Exception('Normalization is not implemented!');
    }
}
