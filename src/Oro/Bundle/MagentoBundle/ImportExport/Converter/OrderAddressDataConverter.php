<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

class OrderAddressDataConverter extends AbstractAddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            [
                'fax'          => 'fax',
                'customer_id'  => 'customerId',
                'address_type' => 'types:0:name',
                'address_id'   => 'originId'
            ]
        );
    }
}
