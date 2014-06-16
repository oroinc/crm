<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

class CustomerReverseProcessor extends AbstractReverseProcessor
{
    /**
     * This structure for check income Customer entity
     * 'OroCRM\Bundle\MagentoBundle\Entity\Customer' - class for compare,
     * 'fields' - fields for compare( in PropertyAccess style)
     * 'checking' - gets all addresses for comparing
     * 'relation' - describe relation in Customer which needed to be compared
     * 'addresses' - relation field
     * 'method' - field which describes how we can get all relations
     * 'class' - for checking instanceof
     *
     * @var array
     */
    protected $checkEntityClasses = [
        'OroCRM\Bundle\MagentoBundle\Entity\Customer' => [
            'fields'   => [
                ['email', 'contact.primary_email'],
                ['first_name', 'contact.first_name'],
                ['last_name', 'contact.last_name'],
                ['name_prefix', 'contact.name_prefix'],
                ['name_suffix', 'contact.name_suffix'],
                ['birthday', 'contact.birthday'],
                ['gender', 'contact.gender'],
                ['middle_name', 'contact.middle_name'],
            ],
            'checking' => 'contact.addresses',
            'relation' => [
                'addresses' => [
                    'method'   => 'addresses',
                    'class'    => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                    'checking' => 'contact_address.id',
                    'fields'   => [
                        'city'         => ['city', 'contact_address.city'],
                        'organization' => ['organization', 'contact_address.organization'],
                        'country'      => ['country', 'contact_address.country'],
                        'firstName'    => ['first_name', 'contact_address.first_name'],
                        'lastName'     => ['last_name', 'contact_address.last_name'],
                        'middleName'   => ['middle_name', 'contact_address.middle_name'],
                        'postalCode'   => ['postal_code', 'contact_address.postal_code'],
                        'namePrefix'   => ['name_prefix', 'contact_address.name_prefix'],
                        'region'       => ['region', 'contact_address.region'],
                        'regionText'   => ['region_text', 'contact_address.region_text'],
                        'street'       => ['street', 'contact_address.street'],
                        'street2'      => ['street2', 'contact_address.street2'],
                        'nameSuffix'   => ['name_suffix', 'contact_address.name_suffix'],
                        'phone'        => ['phone', 'contact_phone.phone'],
                    ]
                ],
            ]
        ]
    ];
}
