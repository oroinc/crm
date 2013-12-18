<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class OrderAddressDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'firstname'    => 'firstName',
            'lastname'     => 'lastName',
            'middlename'   => 'middleName',
            'prefix'       => 'namePrefix',
            'suffix'       => 'nameSuffix',
            'fax'          => 'fax',
            'telephone'    => 'phone',
            'region'       => 'regionText',
            'region_id'    => 'region',
            'country_id'   => 'country',
            'created_at'   => 'created',
            'updated_at'   => 'updated',
            'company'      => 'organization',
            'postcode'     => 'postalCode',
            'customer_id'  => 'customerId',
            'address_type' => 'types:0'
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
