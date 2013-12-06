<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class CartDataConverter extends AbstractTableDataConverter
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
            'entity_id'             => 'originId',
            'store_id'              => 'store',
            'subtotal'              => 'subTotal',
            'grand_total'           => 'grandTotal',
            'items'                 => 'cartItems',
            'customer_id'           => 'customer:originId',
            'customer_email'        => 'email',
            'customer_tax_class_id' => 'customer:tax_class_id',
            'customer_group_id'     => 'customer:group_id',
            'customer_firstname'    => 'customer:firstname',
            'customer_lastname'     => 'customer:lastname',
            'customer_is_guest'     => 'isGuest',
            'created_at'            => 'createdAt',
            'updated_at'            => 'updatedAt',
            'items_count'           => 'itemsCount',
            'items_qty'             => 'itemsQty',
            'store_to_base_rate'    => 'storeToBaseRate',
            'store_to_quote_rate'   => 'storeToQuoteRate',
            'base_currency_code'    => 'baseCurrencyCode',
            'store_currency_code'   => 'storeCurrencyCode',
            'quote_currency_code'   => 'quoteCurrencyCode',
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
