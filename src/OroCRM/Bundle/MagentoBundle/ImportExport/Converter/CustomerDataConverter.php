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
            'website_id' => 'website:originId',
            'store_id' => 'store:originId',
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

        if (isset($importedRecord['gender']) && !empty($importedRecord['gender'])) {
            $gender = strtolower($importedRecord['gender']);
            if (in_array($gender, [Gender::FEMALE, Gender::MALE])) {
                $importedRecord['gender'] = $gender;
            } else {
                $importedRecord['gender'] = null;
            }
        }

        return $importedRecord;
    }

    /**
     * Get maximum backend header for current entity
     *
     * @return array
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}
