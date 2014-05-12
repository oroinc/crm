<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

class CustomerReverseProcessor extends AbstractReverseProcessor
{
    /** {@inheritdoc} */
    protected $checkEntityClasses = [
        'OroCRM\Bundle\MagentoBundle\Entity\Customer'=> [
            'fields' => [
                'email'      => ['getEmail', 'getPrimaryEmail'],
                'firstname'  => ['getFirstName'],
                'lastname'   => ['getLastName'],
                'prefix'     => ['getNamePrefix'],
                'suffix'     => ['getNameSuffix'],
                'dob'        => ['getBirthday'],
                'gender'     => ['getGender'],
                'middlename' => ['getMiddleName'],
            ],
            'checking' => [
                'method' => 'getContact',
                'class'  => 'OroCRM\Bundle\ContactBundle\Entity\Contact',
            ],
            'relation' => [
                'addresses' => [
                    'method'   => 'getAddresses',
                    'class'    => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                    'checking' => [
                        'method' => 'getContactAddress',
                        'class'  => 'OroCRM\Bundle\ContactBundle\Entity\ContactAddress',
                    ],
                    'fields' => [
                        'city' => ['getCity'],
                        'company' => ['getOrganization'],
                        'country_id' => ['getCountry'],
                        'firstname' => ['getFirstName'],
                        'lastname' => ['getLastName'],
                        'middlename' => ['getMiddleName'],
                        'postcode' => ['getPostalCode'],
                        'prefix' => ['getNamePrefix'],
                        'region_id' => ['getRegion'],
                        'region' => ['getRegionText'],
                        'street' => ['getStreet'],
                        'suffix' => ['getNameSuffix'],
                    ]
                ],
            ]
        ]
    ];
}
