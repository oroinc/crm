<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class OrderItemDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'item_id'          => 'originId',
            'product_type'     => 'productType',
            'product_options'  => 'productOptions',
            'is_virtual'       => 'isVirtual',
            'original_price'   => 'originalPrice',
            'discount_percent' => 'discountPercent',
            'qty_ordered'      => 'qty',
            'row_weight'       => 'weight',
            'tax_percent'      => 'taxPercent',
            'tax_amount'       => 'taxAmount',
            'discount_amount'  => 'discountAmount',
            'row_total'        => 'rowTotal'
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
