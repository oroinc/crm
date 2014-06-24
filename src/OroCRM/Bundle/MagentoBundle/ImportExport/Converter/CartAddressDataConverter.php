<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

class CartAddressDataConverter extends AddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            array(
                'address_id' => 'originId',
            )
        );
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
