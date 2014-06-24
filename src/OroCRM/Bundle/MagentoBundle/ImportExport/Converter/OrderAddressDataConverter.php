<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

class OrderAddressDataConverter extends AddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            array(
                'fax'          => 'fax',
                'telephone'    => 'phone',
                'company'      => 'organization',
                'customer_id'  => 'customerId',
                'address_type' => 'types:0:name'
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
