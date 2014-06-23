<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

class MagentoAddressDataConverter extends AddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            array(
                'customerAddressId' => 'originId'
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
