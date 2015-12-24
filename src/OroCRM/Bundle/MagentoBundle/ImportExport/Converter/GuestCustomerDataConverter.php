<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class GuestCustomerDataConverter extends AbstractTreeDataConverter
{
    /**
     * @var array
     */
    public static $conversionRules = [
        'customerEmail' => 'email',
        'customer_firstname' => 'firstName',
        'customer_lastname' => 'lastName',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
        'store_id' => 'store:originId',
        'storeName' => 'createdIn',
    ];

    /**
     * @param array $orderData
     * @return array
     */
    public static function extractCustomersValues(array $orderData)
    {
        $customerData = array_intersect_key($orderData, self::$conversionRules);

        if (!empty($orderData['store']['originId'])) {
            $customerData['store_id'] = $orderData['store']['originId'];
        }

        return $customerData;
    }

    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return self::$conversionRules;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if ($this->context && $this->context->hasOption('channel')) {
            $importedRecord['store:channel:id'] = $this->context->getOption('channel');
            $importedRecord['website:channel:id'] = $this->context->getOption('channel');
            $importedRecord['group:channel:id'] = $this->context->getOption('channel');
        }

        // extract view from 'website\nstore\view' string
        if (!empty($importedRecord['storeName'])) {
            $createdIn = explode("\n", $importedRecord['storeName']);
            $importedRecord['storeName'] = end($createdIn);
        }

        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        $importedRecord = AttributesConverterHelper::addUnknownAttributes($importedRecord, $this->context);

        $importedRecord['confirmed'] = false;
        $importedRecord['guest'] = true;

        return $importedRecord;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
