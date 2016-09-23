<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;

class CartDataConverter extends AbstractTreeDataConverter
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
            'subtotal'            => 'subTotal',
            'grand_total'         => 'grandTotal',
            'items'               => 'cartItems',
            'customer_id'         => 'customer:originId',
            'customer_email'      => 'email',
            'customer_group_id'   => 'customer:group:originId',
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
            'billing_address'     => 'billingAddress',
            'shipping_address'    => 'shippingAddress',
            'cart_status'         => 'status:name'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($this->context && $this->context->hasOption('channel')) {
            $importedRecord['store:channel:id'] = $this->context->getOption('channel');
            $importedRecord['customer:channel:id'] = $this->context->getOption('channel');
            $importedRecord['customer:group:channel:id'] = $this->context->getOption('channel');
        }

        $importedRecord['cart_status'] = CartStatus::STATUS_OPEN;
        if (array_key_exists('is_active', $importedRecord)) {
            $importedRecord['cart_status'] = $importedRecord['is_active'] ?
                CartStatus::STATUS_OPEN : CartStatus::STATUS_EXPIRED;
        }
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);

        return AttributesConverterHelper::addUnknownAttributes($importedRecord, $this->context);
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
