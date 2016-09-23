<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\AddressBundle\Entity\AddressType;

class CustomerAddressDataConverter extends AbstractAddressDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return array_merge(
            parent::getHeaderConversionRules(),
            [
                'customer_address_id' => 'originId',
                'customer_id'         => 'owner:originId',
                'region_id'           => 'region:combinedCode', // Note, this is integer identifier of magento region
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);
        $importedRecord = AttributesConverterHelper::addUnknownAttributes($importedRecord, $this->context);

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
    protected function convertImportedRegion(array $importedRecord)
    {
        if (empty($importedRecord['region']['combinedCode'])) {
            $importedRecord['region'] = null;
        } else {
            $importedRecord['region']['combinedCode'] = (string)$importedRecord['region']['combinedCode'];
        }

        return $importedRecord;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_merge(parent::getBackendHeader(), ['types:0:name', 'types:1:name']);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $exportedRecord = parent::convertToExportFormat($exportedRecord, $skipNullValues);
        $exportedRecord = $this->convertTypesToExportFormat($exportedRecord);

        unset(
            $exportedRecord['created_at'],
            $exportedRecord['updated_at']
        );

        return $exportedRecord;
    }

    /**
     * @param array $exportedRecord
     * @return array
     */
    protected function convertTypesToExportFormat(array $exportedRecord)
    {
        $exportedRecord = array_merge(
            $exportedRecord,
            [
                'is_default_billing' => false,
                'is_default_shipping' => false
            ]
        );

        if (isset($exportedRecord['types:0:name'])) {
            $exportedRecord = array_merge(
                $exportedRecord,
                $this->getMagentoTypeData($exportedRecord['types:0:name'])
            );
            unset($exportedRecord['types:0:name']);
        }
        if (isset($exportedRecord['types:1:name'])) {
            $exportedRecord = array_merge(
                $exportedRecord,
                $this->getMagentoTypeData($exportedRecord['types:1:name'])
            );
            unset($exportedRecord['types:1:name']);
        }

        return $exportedRecord;
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getMagentoTypeData($type)
    {
        if ($type === AddressType::TYPE_BILLING) {
            return ['is_default_billing' => true];
        }
        if ($type === AddressType::TYPE_SHIPPING) {
            return ['is_default_shipping' => true];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function fillEmptyColumns(array $header, array $data)
    {
        $dataDiff = array_diff(array_keys($data), $header);
        $data = array_diff_key($data, array_flip($dataDiff));

        return parent::fillEmptyColumns($header, $data);
    }
}
