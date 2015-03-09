<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\AbstractTreeDataConverter;
use Oro\Bundle\UserBundle\Model\Gender;

class CustomerDataConverter extends AbstractTreeDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
//             create
            'email' => 'email',
            'firstname' => 'firstName',
            'lastname' => 'lastName',
//            'password' => 'password',
//            'website_id' => 'website:originId', // Store is set from Iterator
//            'store_id' => 'store:originId', // Website is set from Iterator
            'group_id' => 'group:originId',
            'prefix' => 'namePrefix',
            'suffix' => 'nameSuffix',
            'dob' => 'birthday',
            'taxvat' => 'vat',
            'gender' => 'gender',
            'middlename' => 'middleName',
//             list
            'customer_id' => 'originId',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
//            'increment_id' => 'incrementId',
//            'created_in' => 'createdIn',
//            'confirmation' => 'confirmation',
//            'password_hash' => 'passwordHash',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);

        if (!empty($importedRecord['birthday'])) {
            $importedRecord['birthday'] = substr($importedRecord['birthday'], 0, 10);
        }

        if (!empty($importedRecord['gender'])) {
            $importedRecord['gender'] = strtolower($importedRecord['gender']);
            if (!in_array($importedRecord['gender'], [Gender::FEMALE, Gender::MALE], true)) {
                $importedRecord['gender'] = null;
            }
        }

        if (!empty($importedRecord['store']) && !empty($importedRecord['website'])) {
            $importedRecord['store']['website'] = $importedRecord['website'];
        }

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
