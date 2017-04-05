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
            'state' => 'status:id',
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
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($this->context && $this->context->hasOption('channel')) {
            $importedRecord['store:channel:id'] = $this->context->getOption('channel');
            $importedRecord['order:channel:id'] = $this->context->getOption('channel');
        }

        // normalize credit memo items if single is passed
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

        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        $importedRecord = AttributesConverterHelper::addUnknownAttributes($importedRecord, $this->context);

        return $importedRecord;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
