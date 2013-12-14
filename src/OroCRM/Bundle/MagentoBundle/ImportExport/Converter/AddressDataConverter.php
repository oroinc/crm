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
            'address_id' => 'originId',
            'firstname'  => 'firstName',
            'lastname'   => 'lastName',
            'middlename' => 'middleName',
            'prefix'     => 'namePrefix',
            'suffix'     => 'nameSuffix',
            'region'     => 'regionText',
            'region_id'  => 'region',
            'country_id' => 'country',
            'created_at' => 'created',
            'updated_at' => 'updated',
            'postcode'   => 'postalCode',
            'customer_id'=> 'customerId',
        ];
    }

    /**
     * Get maximum backend header for current entity
     *
     * @return array
     */
    protected function getBackendHeader()
    {
        // TODO: Implement getBackendHeader() method. [export]
    }
}
