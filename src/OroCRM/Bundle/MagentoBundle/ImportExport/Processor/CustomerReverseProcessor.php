<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

class CustomerReverseProcessor extends AbstractReverseProcessor
{
    /**
     * {@inheritdoc}
     *
     * @todo:
     * - взять все адреса в кастомере и пройтись по ним ( customer->addressses )
     *   - если нет связи в кастомер адресе с contact_address то этот адрес идёт на удаление
     *   - записать все contact_address id
     * - взять контакт адреса у данного кастомера (customer->contact->addresses)
     *   - убрать те кторые есть в списке
     *   - оставшиеся записать как новые для данного кастомера
     */
    protected $checkEntityClasses = [
        'OroCRM\Bundle\MagentoBundle\Entity\Customer'=> [
            'fields' => [
                'email'      => ['email',       'contact.primary_email'],
                'firstname'  => ['first_name',  'contact.first_name'],
                'lastname'   => ['last_name',   'contact.last_name'],
                'prefix'     => ['name_prefix', 'contact.name_prefix'],
                'suffix'     => ['name_suffix', 'contact.name_suffix'],
                'dob'        => ['birthday',    'contact.birthday'],
                'gender'     => ['gender',      'contact.gender'],
                'middlename' => ['middle_name', 'contact.middle_name'],
            ],
            'checking' => 'contact.addresses',
            'relation' => [
                'addresses' => [
                    'method'   => 'addresses',
                    'class'    => 'OroCRM\Bundle\MagentoBundle\Entity\Address',
                    'checking'   => 'contact_address.id',
                    'fields' => [
                        'city' => ['city', 'contact_address.city'],
                        /*'company' => ['addresses.organization'],
                        'country_id' => ['addresses.country'],
                        'firstname' => ['getFirstName'],
                        'lastname' => ['getLastName'],
                        'middlename' => ['getMiddleName'],
                        'postcode' => ['getPostalCode'],
                        'prefix' => ['getNamePrefix'],
                        'region_id' => ['getRegion'],
                        'region' => ['getRegionText'],
                        'street' => ['getStreet'],
                        'suffix' => ['getNameSuffix'],*/
                    ]
                ],
            ]
        ]
    ];
}
