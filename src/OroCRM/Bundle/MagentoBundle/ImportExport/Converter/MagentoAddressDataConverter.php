<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\AddressBundle\Entity\AddressType;

class MagentoAddressDataConverter extends AddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            [
                'customer_address_id' => 'originId'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);

        if (!empty($importedRecord['is_default_shipping'])) {
            $importedRecord['types'][] = ['name' => AddressType::TYPE_SHIPPING];
            unset($importedRecord['is_default_shipping']);
        }
        if (!empty($importedRecord['is_default_billing'])) {
            $importedRecord['types'][] = ['name' => AddressType::TYPE_BILLING];
            unset($importedRecord['is_default_billing']);
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
}
