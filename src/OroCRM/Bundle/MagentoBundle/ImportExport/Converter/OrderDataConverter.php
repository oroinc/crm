<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class OrderDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'increment_id'        => 'incrementId',
            'store_id'            => 'store:originId',
            'store_code'          => 'store:code',
            'store_website_id'    => 'store:website:originId',
            'store_website_code'  => 'store:website:code',
            'store_website_name'  => 'store:website:name',
            'customer_id'         => 'owner:originId',
            'is_virtual'          => 'isVirtual',
            'customer_is_guest'   => 'isGuest',
            'gift_message_body'   => 'giftMessage',
            'remote_ip'           => 'remoteIp',
            'store_name'          => 'storeName',
            'total_paid'          => 'totalPaidAmount',
            'total_invoiced'      => 'totalInvoiced',
            'total_refunded'      => 'totalRefunded',
            'total_canceled'      => 'totalCanceled',
            'quote_id'            => 'cart:originId',
            'payment_method'      => 'paymentMethod',
            'order_currency_code' => 'currency',
            'subtotal'            => 'subtotalAmount',
            'shipping_amount'     => 'shippingAmount',
            'shipping_method'     => 'shippingMethod',
            'tax_amount'          => 'taxAmount',
            'discount_amount'     => 'discountAmount',
            'grand_total'         => 'totalAmount',
            'created_at'          => 'createdAt',
            'updated_at'          => 'updatedAt'
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
