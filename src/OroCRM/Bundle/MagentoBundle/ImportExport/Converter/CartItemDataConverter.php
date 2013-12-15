<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class CartItemDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'item_id'         => 'originId',
            'product_id'      => 'productId',
            'parent_item_id'  => 'parentItemId',
            'free_shipping'   => 'freeShipping',
            'gift_message'    => 'giftMessage',
            'tax_class_id'    => 'taxClassId',
            'is_virtual'      => 'isVirtual',
            'product_type'    => 'productType',
            'discount_amount' => 'discountAmount',
            'tax_percent'     => 'taxPercent',
            'price_incl_tax'  => 'priceInclTax',
            'row_total'       => 'rowTotal',
            'tax_amount'      => 'taxAmount',
            'created_at'      => 'createdAt',
            'updated_at'      => 'updatedAt',
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
