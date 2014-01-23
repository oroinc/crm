<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class CartDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'entity_id'           => 'originId',
            'store_id'            => 'store:originId',
            'store_code'          => 'store:code',
            'store_storename'     => 'store:name',
            'store_website_id'    => 'store:website:originId',
            'store_website_code'  => 'store:website:code',
            'store_website_name'  => 'store:website:name',
            'subtotal'            => 'subTotal',
            'grand_total'         => 'grandTotal',
            'items'               => 'cartItems',
            'customer_id'         => 'customer:originId',
            'customer_email'      => 'email',
            'customer_group_id'   => 'customer:group:originId',
            'customer_group_code' => 'customer:group:code',
            'customer_group_name' => 'customer:group:name',
            'customer_firstname'  => 'customer:firstName',
            'customer_lastname'   => 'customer:lastName',
            'customer_is_guest'   => 'isGuest',
            'created_at'          => 'createdAt',
            'updated_at'          => 'updatedAt',
            'items_count'         => 'itemsCount',
            'items_qty'           => 'itemsQty',
            'store_to_base_rate'  => 'storeToBaseRate',
            'store_to_quote_rate' => 'storeToQuoteRate',
            'base_currency_code'  => 'baseCurrencyCode',
            'store_currency_code' => 'storeCurrencyCode',
            'quote_currency_code' => 'quoteCurrencyCode',
            'shipping_address_id' => 'shipping_address:originId',
            'billing_address_id'  => 'billing_address:originId',
            'payment'             => 'paymentDetails',
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
