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
            'email' => 'email',
            'firstname' => 'firstName',
            'lastname' => 'lastName',
//            'password' => 'password', // TODO: Add ability to set and change? customer password
            'group_id' => 'group:originId',
            'prefix' => 'namePrefix',
            'suffix' => 'nameSuffix',
            'dob' => 'birthday',
            'taxvat' => 'vat',
            'gender' => 'gender',
            'middlename' => 'middleName',
            'customer_id' => 'originId',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
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
            $importedRecord['gender'] = $this->getOroGender($importedRecord['gender']);
        }

        if (!empty($importedRecord['store']) && !empty($importedRecord['website'])) {
            $importedRecord['store']['website'] = $importedRecord['website'];
        }

        if ($this->context && !empty($importedRecord['group']) && $this->context->hasOption('channel')) {
            $importedRecord['group']['channel']['id'] = $this->context->getOption('channel');
        }

        return $importedRecord;
    }

    /**
     * @param string|int $gender
     * @return null|string
     */
    protected function getOroGender($gender)
    {
        if (is_integer($gender)) {
            if ($gender == 1) {
                $gender = Gender::MALE;
            }
            if ($gender == 2) {
                $gender = Gender::FEMALE;
            }
        } else {
            $gender = strtolower($gender);
            if (!in_array($gender, [Gender::FEMALE, Gender::MALE], true)) {
                $gender = null;
            }
        }

        return $gender;
    }

    /**
     * @param string $gender
     * @return int|null
     */
    protected function getMagentoGender($gender)
    {
        if ($gender) {
            if ($gender === Gender::MALE) {
                return 1;
            }
            if ($gender === Gender::FEMALE) {
                return 2;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }

    /**
     * {@inheritdoc}
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        $exportedRecord = parent::convertToExportFormat($exportedRecord, $skipNullValues);

        if (isset($exportedRecord['store']['store_id'])) {
            $exportedRecord['store_id'] =  $exportedRecord['store']['store_id'];
            unset($exportedRecord['store']);
        }

        if (isset($exportedRecord['website']['id'])) {
            $exportedRecord['website_id'] =  $exportedRecord['website']['id'];
            unset($exportedRecord['website']);
        }

        if (isset($exportedRecord['group']['customer_group_id'])) {
            $exportedRecord['group_id'] =  $exportedRecord['group']['customer_group_id'];
            unset($exportedRecord['group']);
        }

        if (isset($exportedRecord['created_at'])) {
            unset($exportedRecord['created_at']);
        }

        if (isset($exportedRecord['updated_at'])) {
            unset($exportedRecord['updated_at']);
        }

        if (!empty($exportedRecord['gender'])) {
            $exportedRecord['gender'] = $this->getMagentoGender($exportedRecord['gender']);
        }

        return $exportedRecord;
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
