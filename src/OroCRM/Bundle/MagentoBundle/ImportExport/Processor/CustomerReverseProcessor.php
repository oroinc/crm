<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

class CustomerReverseProcessor extends AbstractReverseProcessor
{
    /**
     * @var array
    */
    protected $checkEntityClasses = [
        'OroCRM\Bundle\MagentoBundle\Entity\Customer'=> [
            'fields' => [
                ['email',       'contact.primary_email'],
                ['first_name',  'contact.first_name'],
                ['last_name',   'contact.last_name'],
                ['name_prefix', 'contact.name_prefix'],
                ['name_suffix', 'contact.name_suffix'],
                ['birthday',    'contact.birthday'],
                ['gender',      'contact.gender'],
                ['middle_name', 'contact.middle_name'],
            ],
            'checking' => 'contact.addresses',
            'relation' => [
                'addresses' => [
                    'method'   => 'addresses',
                    'class'    => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                    'checking'   => 'contact_address.id',
                    'fields' => [
                        ['city',         'contact_address.city'],
                        ['organization', 'contact_address.organization'],
                        ['country',      'contact_address.country', 'iso2_code'],
                        ['first_name',   'contact_address.first_name'],
                        ['last_name',    'contact_address.last_name'],
                        ['middle_name',  'contact_address.middle_name'],
                        ['postal_code',  'contact_address.postal_code'],
                        ['name_prefix',  'contact_address.name_prefix'],
                        ['region',       'contact_address.region', 'combined_code'],
                        ['region_text',  'contact_address.region_text'],
                        ['street',       'contact_address.street'],
                        ['name_suffix',  'contact_address.name_suffix'],
                    ]
                ],
            ]
        ]
    ];
}
