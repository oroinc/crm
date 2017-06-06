<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class CreditMemoItemDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'item_id'          => 'originId',
            'order_item_id'    => 'orderItemId',
            'price'            => 'price',
            'qty'              => 'qty',
            'tax_amount'       => 'taxAmount',
            'discount_amount'  => 'discountAmount',
            'row_total'        => 'rowTotal',
            'sku'              => 'sku',
            'name'             => 'name',
            'additional_data'  => 'additionalData',
            'description'      => 'description'
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
