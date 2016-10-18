<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

class CartAddressDataConverter extends AbstractAddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            [
                'address_id' => 'originId',
                'telephone'  => 'phone',
            ]
        );
    }
}
