<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class CartItemDataConverter extends AbstractTableDataConverter
{
    /**
     * Get list of rules that should be used to convert,
     *
     * Example: array(
     *     'User Name' => 'userName', // key is frontend hint, value is backend hint
     *     'User Group' => array(     // convert data using regular expression
     *         self::FRONTEND_TO_BACKEND => array('User Group (\d+)', 'userGroup:$1'),
     *         self::BACKEND_TO_FRONTEND => array('userGroup:(\d+)', 'User Group $1'),
     *     )
     * )
     *
     * @return array
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
     * Get maximum backend header for current entity
     *
     * @return array
     */
    protected function getBackendHeader()
    {
        // TODO: Implement getBackendHeader() method. [export]
    }
}
