<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class CreditMemoDataConverter extends AbstractTreeDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'creditmemo_id' => 'originId',
            'increment_id' => 'incrementId',
            'order_id' => 'order:originId',
            'store_id' => 'store:originId',
            'state' => 'status',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'invoice_id' => 'invoiceId',
            'transaction_id' => 'transactionId',
            'email_sent' => 'emailSent',
            'adjustment_negative' => 'adjustmentNegative',
            'shipping_amount' => 'shippingAmount',
            'grand_total' => 'grandTotal',
            'adjustment_positive' => 'adjustmentPositive',
            'customer_bal_total_refunded' => 'customerBalTotalRefunded',
            'reward_points_balance_refund' => 'rewardPointsBalanceRefund',
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
