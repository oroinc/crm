<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class NewsletterSubscriberDataConverter extends AbstractTreeDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'subscriber_id' => 'originId',
            'change_status_at' => 'changeStatusAt',
            'customer_id' => 'customer:originId',
            'subscriber_email' => 'email',
            'subscriber_status' => 'status:id',
            'subscriber_confirm_code' => 'confirmCode',
            'store_id' => 'store:originId'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($this->context && $this->context->hasOption('channel')) {
            $importedRecord['customer:channel:id'] = $this->context->getOption('channel');
            $importedRecord['store:channel:id'] = $this->context->getOption('channel');
        }

        if (array_key_exists('customer_id', $importedRecord)) {
            $importedRecord['customer_id'] = filter_var($importedRecord['customer_id'], FILTER_SANITIZE_NUMBER_INT);
        }

        if (array_key_exists('subscriber_email', $importedRecord) && is_string($importedRecord['subscriber_email'])) {
            $importedRecord['subscriber_email'] = trim($importedRecord['subscriber_email']);
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $exportedRecord = parent::convertToExportFormat($exportedRecord, $skipNullValues);

        // store_id can be 0
        if (isset($exportedRecord['store']['store_id'])) {
            $exportedRecord['store_id'] = $exportedRecord['store']['store_id'];
            unset($exportedRecord['store']);
        }

        // Do not clear confirmCode until its empty
        if (empty($exportedRecord['subscriber_confirm_code'])) {
            unset($exportedRecord['subscriber_confirm_code']);
        }

        return $exportedRecord;
    }


    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
