<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class OrderDataConverter extends AbstractTreeDataConverter
{
    /** @var string[] */
    protected $arrayNodeKeys = [
        'items',
        'status_history'
    ];

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'increment_id'        => 'incrementId',
            'order_id'            => 'originId',
            'store_id'            => 'store:originId',
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
            'coupon_code'         => 'couponCode',
            'status_history'      => 'orderNotes'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        foreach ($this->arrayNodeKeys as $key) {
            if (!empty($importedRecord[$key])) {
                $this->fixNodeWithSingleRecord($importedRecord, $key);
            }
        }

        if ($this->context && $this->context->hasOption('channel')) {
            $importedRecord['store:channel:id'] = $this->context->getOption('channel');
            $importedRecord['customer:channel:id'] = $this->context->getOption('channel');
            $importedRecord['cart:channel:id'] = $this->context->getOption('channel');
        }

        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        $importedRecord = AttributesConverterHelper::addUnknownAttributes($importedRecord, $this->context);

        if (!empty($importedRecord['paymentDetails']['method'])) {
            $importedRecord['paymentMethod'] = $importedRecord['paymentDetails']['method'];
        } else {
            $importedRecord['paymentMethod'] = null;
        }

        if (isset($importedRecord['paymentDetails']['method'])) {
            unset($importedRecord['paymentDetails']['method']);
        }

        return $importedRecord;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        // will be implemented for bidirectional sync
        throw new \Exception('Normalization is not implemented!');
    }

    /**
     * @param mixed[]   $importedRecord
     * @param string    $key
     */
    private function fixNodeWithSingleRecord(array &$importedRecord, $key)
    {
        // normalize order rows if single is passed
        if (!empty($importedRecord[$key])) {
            /** @var array $data */
            $data = $importedRecord[$key];
            foreach ($data as $item) {
                if (!is_array($item)) {
                    $importedRecord[$key] = [$data];
                    break;
                }
            }
        }
    }
}
