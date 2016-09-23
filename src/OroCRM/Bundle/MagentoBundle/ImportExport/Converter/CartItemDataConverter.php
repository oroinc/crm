<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class CartItemDataConverter extends IntegrationAwareDataConverter
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
            'product_image_url' => 'productImageUrl',
            'product_url'     => 'productUrl'
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
