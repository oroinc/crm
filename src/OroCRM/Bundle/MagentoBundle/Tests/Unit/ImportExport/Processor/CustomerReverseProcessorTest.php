<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\CustomerReverseProcessor;

class CustomerReverseProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Customer */
    protected $customer;

    /** @var Contact */
    protected $contact;

    /** @var Address */
    protected $address;

    /** @var ContactAddress */
    protected $contactAddress;

    /** @var Channel */
    protected $channel;

    /** @var Country */
    protected $country;

    /** @var Region */
    protected $region;

    /** @var ContactPhone */
    protected $contactPhone;

    public function setUp()
    {
        $this->channel        = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $this->customer       = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer');
        $this->contact        = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $this->address        = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Address');
        $this->contactAddress = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\ContactAddress');
        $this->contactPhone   = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\ContactPhone');
        $this->country        = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()->getMock();
        $this->region         = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Region')
            ->disableOriginalConstructor()->getMock();

        $collection = $this->getMock('Doctrine\Common\Collections\Collection');

        $collection->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue([$this->address]));

        $this->country->expects($this->any())
            ->method('getIso2Code')
            ->will($this->returnValue('US'));

        $this->region->expects($this->any())
            ->method('getCombinedCode')
            ->will($this->returnValue('US-US'));

        $this->contactAddress->expects($this->any())
            ->method('getCountry')
            ->will($this->returnValue($this->country));

        $this->contactAddress->expects($this->any())
            ->method('getRegion')
            ->will($this->returnValue($this->region));

        $this->customer->expects($this->any())
            ->method('getContact')
            ->will($this->returnValue($this->contact));

        $this->customer
            ->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($this->channel));

        $this->address
            ->expects($this->any())
            ->method('getContactAddress')
            ->will($this->returnValue($this->contactAddress));

        $this->address
            ->expects($this->any())
            ->method('getContactPhone')
            ->will($this->returnValue($this->contactPhone));

        $this->address
            ->expects($this->any())
            ->method('getCountry')
            ->will($this->returnValue($this->country));

        $this->address
            ->expects($this->any())
            ->method('getRegion')
            ->will($this->returnValue($this->region));

        $this->customer
            ->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue($collection));
    }

    public function tearDown()
    {
        unset(
        $this->customer,
        $this->contact,
        $this->address,
        $this->contactAddress
        );
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function getDataProvider()
    {
        $email               = 'e@e.com';
        $firstName           = 'john';
        $lastName            = 'Doe';
        $prefix              = '';
        $suffix              = 'suffix';
        $dob                 = '01.01.1975';
        $gender              = 'male';
        $middleName          = '';
        $cityAddress         = 'city';
        $organizationAddress = 'oro';
        $countryAddress      = 'US';
        $firstNameAddress    = 'John';
        $lastNameAddress     = 'Doe';
        $middleNameAddress   = '';
        $postalCodeAddress   = '12345';
        $prefixAddress       = '';
        $regionAddress       = 'US-US';
        $regionTextAddress   = 'text';
        $streetAddress       = '';
        $nameSuffixAddress   = '';
        $phone               = '+380501231212';

        return [
            [
                'partial entry' =>
                    [
                        'email'                      => 'e1@e.com',
                        'emailContact'               => $email,
                        'firstName'                  => $firstName,
                        'firstNameContact'           => $firstName,
                        'lastName'                   => 'Smith',
                        'lastNameContact'            => $lastName,
                        'prefix'                     => $prefix,
                        'prefixContact'              => $prefix,
                        'suffix'                     => '',
                        'suffixContact'              => $suffix,
                        'dob'                        => $dob,
                        'dobContact'                 => $dob,
                        'gender'                     => 'S',
                        'genderContact'              => $gender,
                        'middleName'                 => $middleName,
                        'middleNameContact'          => $middleName,
                        'cityAddress'                => '',
                        'cityContactAddress'         => $cityAddress,
                        'organizationAddress'        => '',
                        'organizationContactAddress' => $organizationAddress,
                        'countryAddress'             => $countryAddress,
                        'countryContactAddress'      => $countryAddress,
                        'firstNameAddress'           => $firstNameAddress,
                        'firstNameContactAddress'    => $firstNameAddress,
                        'lastNameAddress'            => $lastNameAddress,
                        'lastNameContactAddress'     => $lastNameAddress,
                        'middleNameAddress'          => $middleNameAddress,
                        'middleNameContactAddress'   => $middleNameAddress,
                        'postalCodeAddress'          => $postalCodeAddress,
                        'postalCodeContactAddress'   => $postalCodeAddress,
                        'prefixAddress'              => $prefixAddress,
                        'prefixContactAddress'       => $prefixAddress,
                        'regionAddress'              => $regionAddress,
                        'regionContactAddress'       => $regionAddress,
                        'regionTextAddress'          => $regionTextAddress,
                        'regionTextContactAddress'   => $regionTextAddress,
                        'streetAddress'              => $streetAddress,
                        'streetContactAddress'       => $streetAddress,
                        'nameSuffixAddress'          => $nameSuffixAddress,
                        'nameSuffixContactAddress'   => $nameSuffixAddress,
                        'phone'                      => $phone,
                    ],
                (object)[
                    'object' => [
                        'email'       => $email,
                        'last_name'   => $lastName,
                        'name_suffix' => $suffix,
                        'gender'      => $gender,
                        'addresses'   => [
                            [
                                'object' => [
                                    'city'         => $cityAddress,
                                    'organization' => $organizationAddress,
                                ],
                                'status' => 'update',
                                'entity' => ''
                            ]
                        ]
                    ]
                ]
            ],
            [
                'full entry' =>
                    [
                        'email'                      => 'e1@e.com',
                        'emailContact'               => $email,
                        'firstName'                  => 'jane',
                        'firstNameContact'           => $firstName,
                        'lastName'                   => 'Smith',
                        'lastNameContact'            => $lastName,
                        'prefix'                     => 'prefix',
                        'prefixContact'              => $prefix,
                        'suffix'                     => '',
                        'suffixContact'              => $suffix,
                        'dob'                        => '01.02.1975',
                        'dobContact'                 => $dob,
                        'gender'                     => 'S',
                        'genderContact'              => $gender,
                        'middleName'                 => 'middle',
                        'middleNameContact'          => $middleName,
                        'cityAddress'                => '',
                        'cityContactAddress'         => $cityAddress,
                        'organizationAddress'        => '',
                        'organizationContactAddress' => $organizationAddress,
                        'countryAddress'             => '',
                        'countryContactAddress'      => $countryAddress,
                        'firstNameAddress'           => '',
                        'firstNameContactAddress'    => $firstNameAddress,
                        'lastNameAddress'            => '',
                        'lastNameContactAddress'     => $lastNameAddress,
                        'middleNameAddress'          => 'middle',
                        'middleNameContactAddress'   => $middleNameAddress,
                        'postalCodeAddress'          => '12458',
                        'postalCodeContactAddress'   => $postalCodeAddress,
                        'prefixAddress'              => 'pref1',
                        'prefixContactAddress'       => $prefixAddress,
                        'regionAddress'              => 'reg',
                        'regionContactAddress'       => $regionAddress,
                        'regionTextAddress'          => 'retext',
                        'regionTextContactAddress'   => $regionTextAddress,
                        'streetAddress'              => 'str',
                        'streetContactAddress'       => $streetAddress,
                        'nameSuffixAddress'          => 'suf',
                        'nameSuffixContactAddress'   => $nameSuffixAddress,
                        'phone'                      => $phone,
                    ],
                (object)[
                    'object' => [
                        'email'       => $email,
                        'first_name'  => $firstName,
                        'last_name'   => $lastName,
                        'name_prefix' => $prefix,
                        'name_suffix' => $suffix,
                        'birthday'    => $dob,
                        'gender'      => $gender,
                        'middle_name' => $middleName,
                        'addresses'   => [
                            [
                                'object' => [
                                    'city'         => $cityAddress,
                                    'organization' => $organizationAddress,
                                    'firstName'    => $firstNameAddress,
                                    'lastName'     => $lastNameAddress,
                                    'middleName'   => $middleNameAddress,
                                    'postalCode'   => $postalCodeAddress,
                                    'namePrefix'   => $prefixAddress,
                                    'regionText'   => $regionTextAddress,
                                    'street'       => $streetAddress,
                                    'nameSuffix'   => $nameSuffixAddress,
                                ],
                                'status' => 'update',
                                'entity' => '',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'nothing to change' =>
                    [
                        'email'                      => $email,
                        'emailContact'               => $email,
                        'firstName'                  => $firstName,
                        'firstNameContact'           => $firstName,
                        'lastName'                   => $lastName,
                        'lastNameContact'            => $lastName,
                        'prefix'                     => $prefix,
                        'prefixContact'              => $prefix,
                        'suffix'                     => $suffix,
                        'suffixContact'              => $suffix,
                        'dob'                        => $dob,
                        'dobContact'                 => $dob,
                        'gender'                     => $gender,
                        'genderContact'              => $gender,
                        'middleName'                 => $middleName,
                        'middleNameContact'          => $middleName,
                        'cityAddress'                => $cityAddress,
                        'cityContactAddress'         => $cityAddress,
                        'organizationAddress'        => $organizationAddress,
                        'organizationContactAddress' => $organizationAddress,
                        'countryAddress'             => $countryAddress,
                        'countryContactAddress'      => $countryAddress,
                        'firstNameAddress'           => $firstNameAddress,
                        'firstNameContactAddress'    => $firstNameAddress,
                        'lastNameAddress'            => $lastNameAddress,
                        'lastNameContactAddress'     => $lastNameAddress,
                        'middleNameAddress'          => $middleNameAddress,
                        'middleNameContactAddress'   => $middleNameAddress,
                        'postalCodeAddress'          => $postalCodeAddress,
                        'postalCodeContactAddress'   => $postalCodeAddress,
                        'prefixAddress'              => $prefixAddress,
                        'prefixContactAddress'       => $prefixAddress,
                        'regionAddress'              => $regionAddress,
                        'regionContactAddress'       => $regionAddress,
                        'regionTextAddress'          => $regionTextAddress,
                        'regionTextContactAddress'   => $regionTextAddress,
                        'streetAddress'              => $streetAddress,
                        'streetContactAddress'       => $streetAddress,
                        'nameSuffixAddress'          => $nameSuffixAddress,
                        'nameSuffixContactAddress'   => $nameSuffixAddress,
                        'phone'                      => $phone,
                    ],
                (object)[
                    'object' => [
                        'addresses' => [
                            [
                                'object' => [],
                                'entity' => '',
                                'status' => 'update',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'no originId' =>
                    [
                        'email'                      => $email,
                        'emailContact'               => $email,
                        'firstName'                  => $firstName,
                        'firstNameContact'           => $firstName,
                        'lastName'                   => $lastName,
                        'lastNameContact'            => $lastName,
                        'prefix'                     => $prefix,
                        'prefixContact'              => $prefix,
                        'suffix'                     => $suffix,
                        'suffixContact'              => $suffix,
                        'dob'                        => $dob,
                        'dobContact'                 => $dob,
                        'gender'                     => $gender,
                        'genderContact'              => $gender,
                        'middleName'                 => $middleName,
                        'middleNameContact'          => $middleName,
                        'cityAddress'                => $cityAddress,
                        'cityContactAddress'         => $cityAddress,
                        'organizationAddress'        => $organizationAddress,
                        'organizationContactAddress' => $organizationAddress,
                        'countryAddress'             => $countryAddress,
                        'countryContactAddress'      => $countryAddress,
                        'firstNameAddress'           => $firstNameAddress,
                        'firstNameContactAddress'    => $firstNameAddress,
                        'lastNameAddress'            => $lastNameAddress,
                        'lastNameContactAddress'     => $lastNameAddress,
                        'middleNameAddress'          => $middleNameAddress,
                        'middleNameContactAddress'   => $middleNameAddress,
                        'postalCodeAddress'          => $postalCodeAddress,
                        'postalCodeContactAddress'   => $postalCodeAddress,
                        'prefixAddress'              => $prefixAddress,
                        'prefixContactAddress'       => $prefixAddress,
                        'regionAddress'              => $regionAddress,
                        'regionContactAddress'       => $regionAddress,
                        'regionTextAddress'          => $regionTextAddress,
                        'regionTextContactAddress'   => $regionTextAddress,
                        'streetAddress'              => $streetAddress,
                        'streetContactAddress'       => $streetAddress,
                        'nameSuffixAddress'          => $nameSuffixAddress,
                        'nameSuffixContactAddress'   => $nameSuffixAddress,
                        'phone'                      => $phone,
                    ],
                (object)[
                    'object' => [
                        'addresses' => [
                            [
                                'object' => [],
                                'entity' => '',
                                'status' => 'update',
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD)
     *
     * @dataProvider  getDataProvider
     *
     * @param array     $fields
     * @param \stdClass $checkingObject
     */
    public function testProcess(array $fields, $checkingObject)
    {
        $customerReverseProcessor = new CustomerReverseProcessor();

        $checkingObject->entity = $this->customer;

        $this->customer->expects($this->any())->method('getOriginId')
            ->will($this->returnValue(true));

        $this->customer->expects($this->any())->method('getEmail')
            ->will($this->returnValue($fields['email']));
        $this->contact->expects($this->any())->method('getPrimaryEmail')
            ->will($this->returnValue($fields['emailContact']));

        $this->customer->expects($this->any())->method('getFirstName')
            ->will($this->returnValue($fields['firstName']));
        $this->contact->expects($this->any())->method('getFirstName')
            ->will($this->returnValue($fields['firstNameContact']));

        $this->customer->expects($this->any())->method('getLastName')
            ->will($this->returnValue($fields['lastName']));
        $this->contact->expects($this->any())->method('getLastName')
            ->will($this->returnValue($fields['lastNameContact']));

        $this->customer->expects($this->any())->method('getNamePrefix')
            ->will($this->returnValue($fields['prefix']));
        $this->contact->expects($this->any())->method('getNamePrefix')
            ->will($this->returnValue($fields['prefixContact']));

        $this->customer->expects($this->any())->method('getNameSuffix')
            ->will($this->returnValue($fields['suffix']));
        $this->contact->expects($this->any())->method('getNameSuffix')
            ->will($this->returnValue($fields['suffixContact']));

        $this->customer->expects($this->any())->method('getBirthday')
            ->will($this->returnValue($fields['dob']));
        $this->contact->expects($this->any())->method('getBirthday')
            ->will($this->returnValue($fields['dobContact']));

        $this->customer->expects($this->any())->method('getGender')
            ->will($this->returnValue($fields['gender']));
        $this->contact->expects($this->any())->method('getGender')
            ->will($this->returnValue($fields['genderContact']));

        $this->customer->expects($this->any())->method('getMiddleName')
            ->will($this->returnValue($fields['middleName']));
        $this->contact->expects($this->any())->method('getMiddleName')
            ->will($this->returnValue($fields['middleNameContact']));

        $this->address->expects($this->any())->method('getCity')
            ->will($this->returnValue($fields['cityAddress']));
        $this->contactAddress->expects($this->any())->method('getCity')
            ->will($this->returnValue($fields['cityContactAddress']));

        $this->address->expects($this->any())->method('getOrganization')
            ->will($this->returnValue($fields['organizationAddress']));
        $this->contactAddress->expects($this->any())->method('getOrganization')
            ->will($this->returnValue($fields['organizationContactAddress']));

        $this->address->expects($this->any())->method('getCountry')
            ->will($this->returnValue($fields['countryAddress']));
        $this->contactAddress->expects($this->any())->method('getCountry')
            ->will($this->returnValue($fields['countryContactAddress']));

        $this->address->expects($this->any())->method('getFirstName')
            ->will($this->returnValue($fields['firstNameAddress']));
        $this->contactAddress->expects($this->any())->method('getFirstName')
            ->will($this->returnValue($fields['firstNameContactAddress']));

        $this->address->expects($this->any())->method('getLastName')
            ->will($this->returnValue($fields['lastNameAddress']));
        $this->contactAddress->expects($this->any())->method('getLastName')
            ->will($this->returnValue($fields['lastNameContactAddress']));

        $this->address->expects($this->any())->method('getMiddleName')
            ->will($this->returnValue($fields['middleNameAddress']));
        $this->contactAddress->expects($this->any())->method('getMiddleName')
            ->will($this->returnValue($fields['middleNameContactAddress']));

        $this->address->expects($this->any())->method('getPostalCode')
            ->will($this->returnValue($fields['postalCodeAddress']));
        $this->contactAddress->expects($this->any())->method('getPostalCode')
            ->will($this->returnValue($fields['postalCodeContactAddress']));

        $this->address->expects($this->any())->method('getNamePrefix')
            ->will($this->returnValue($fields['prefixAddress']));
        $this->contactAddress->expects($this->any())->method('getNamePrefix')
            ->will($this->returnValue($fields['prefixContactAddress']));

        $this->address->expects($this->any())->method('getRegion')
            ->will($this->returnValue($fields['regionAddress']));
        $this->contactAddress->expects($this->any())->method('getRegion')
            ->will($this->returnValue($fields['regionContactAddress']));

        $this->address->expects($this->any())->method('getRegionText')
            ->will($this->returnValue($fields['regionTextAddress']));
        $this->contactAddress->expects($this->any())->method('getRegionText')
            ->will($this->returnValue($fields['regionTextContactAddress']));

        $this->address->expects($this->any())->method('getStreet')
            ->will($this->returnValue($fields['streetAddress']));
        $this->contactAddress->expects($this->any())->method('getStreet')
            ->will($this->returnValue($fields['streetContactAddress']));

        $this->address->expects($this->any())->method('getNameSuffix')
            ->will($this->returnValue($fields['nameSuffixAddress']));
        $this->contactAddress->expects($this->any())->method('getNameSuffix')
            ->will($this->returnValue($fields['nameSuffixContactAddress']));

        $this->address->expects($this->any())->method('getPhone')
            ->will($this->returnValue($fields['phone']));

        $this->contactPhone->expects($this->any())->method('getPhone')
            ->will($this->returnValue($fields['phone']));

        $this->contact
            ->expects($this->any())->method('getAddresses')
            ->will($this->returnValue([$this->contactAddress]));

        if (!empty($checkingObject->object['addresses'])) {
            foreach ($checkingObject->object['addresses'] as &$address) {
                $address['entity'] = $this->address;
                if (!empty($address['status'])) {
                    if ('update' === $address['status']) {
                        $address['object']['country'] = $this->country;
                        $address['object']['region'] = $this->region;
                    }
                }
            }
            unset($address);
        }

        $result = $customerReverseProcessor->process($this->customer);

        $this->assertEquals(
            $checkingObject,
            $result
        );
    }
}
