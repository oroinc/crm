<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class OrderDataConverter extends AbstractTreeDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'increment_id'        => 'incrementId',
            'store_id'            => 'store:originId',
            'store_storename'     => 'store:name',
            'store_code'          => 'store:code',
            'store_website_id'    => 'store:website:originId',
            'store_website_code'  => 'store:website:code',
            'store_website_name'  => 'store:website:name',
            'customer_id'         => 'customer:originId',
            'is_virtual'          => 'isVirtual',
            'customer_is_guest'   => 'isGuest',
            'gift_message'        => 'giftMessage',
            'remote_ip'           => 'remoteIp',
            'store_name'          => 'storeName',
            'total_paid'          => 'totalPaidAmount',
            'total_invoiced'      => 'totalInvoicedAmount',
            'total_refunded'      => 'totalRefundedAmount',
            'total_canceled'      => 'totalCanceledAmount',
            'quote_id'            => 'cart:originId',
            'payment_method'      => 'paymentMethod',
            'order_currency_code' => 'currency',
            'subtotal'            => 'subtotalAmount',
            'shipping_amount'     => 'shippingAmount',
            'shipping_method'     => 'shippingMethod',
            'tax_amount'          => 'taxAmount',
            'discount_amount'     => 'discountAmount',
            'grand_total'         => 'totalAmount',
            'payment'             => 'paymentDetails',
            'shipping_address'    => 'addresses:0',
            'billing_address'     => 'addresses:1',
            'created_at'          => 'createdAt',
            'updated_at'          => 'updatedAt',
            'customer_email'      => 'customerEmail',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        // normalize order items if single is passed
        if (!empty($importedRecord['items'])) {
            /** @var array $items */
            $items = $importedRecord['items'];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    $importedRecord['items'] = [$items];

                    break;
                }
            }
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
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
