<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;

class CustomerDataConverter extends ConfigurableTableDataConverter
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
}
