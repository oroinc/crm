<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;

class GuestCustomerDataConverter extends AbstractTreeDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'customerEmail' => 'email',
            'firstname' => 'firstName',
            'lastname' => 'lastName',
            'prefix' => 'namePrefix',
            'suffix' => 'nameSuffix',
            'middlename' => 'middleName',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'store_id' => 'store:originId',
            'created_in' => 'createdIn'
        ];
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
