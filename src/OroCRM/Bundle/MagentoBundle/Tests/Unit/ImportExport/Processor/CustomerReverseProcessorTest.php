<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\CustomerReverseProcessor;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

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

    public function setUp()
    {
        $this->channel  =  $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')->getMock();
        $this->customer = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Customer')->getMock();
        $this->contact  = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')->getMock();
        $this->address = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Address')->getMock();
        $this->contactAddress = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\ContactAddress')->getMock();

        $collection = $this->getMock('Doctrine\Common\Collections\Collection');

        $collection->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue([$this->address]));

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


    public function getDataProvider()
    {
        $email = 'e@e.com';
        $firstName = 'john';
        $lastName  = 'Doe';
        $prefix = '';
        $suffix = 'suffix';
        $dob = '01.01.1975';
        $gender = 'male';
        $middleName = '';
        $cityAddress = 'city';
        $organizationAddress = 'oro';
        $countryAddress = 'US';
        $firstNameAddress = 'John';
        $lastNameAddress = 'Doe';
        $middleNameAddress = '';
        $postalCodeAddress = '12345';
        $prefixAddress = '';
        $regionAddress = '';
        $regionTextAddress = 'text';
        $streetAddress = '';
        $nameSuffixAddress = '';
        $id = 1;
        $originId = 11;

        return [
            [
                'partial entry' =>
                [
                    'id' => $id, 'originId' => $originId,
                    'email' => 'e1@e.com', 'emailContact' => $email,
                    'firstName' => $firstName, 'firstNameContact'=> $firstName,
                    'lastName' => 'Smith', 'lastNameContact' => $lastName,
                    'prefix' => $prefix, 'prefixContact' => $prefix,
                    'suffix' => '', 'suffixContact' => $suffix,
                    'dob' => $dob, 'dobContact' => $dob,
                    'gender' => 'S', 'genderContact' => $gender,
                    'middleName' => $middleName, 'middleNameContact' => $middleName,
                    'cityAddress' => '', 'cityContactAddress' => $cityAddress,
                    'organizationAddress' => '', 'organizationContactAddress' => $organizationAddress,
                    'countryAddress' => $countryAddress, 'countryContactAddress' => $countryAddress,
                    'firstNameAddress' => $firstNameAddress, 'firstNameContactAddress' => $firstNameAddress,
                    'lastNameAddress' => $lastNameAddress, 'lastNameContactAddress' => $lastNameAddress,
                    'middleNameAddress' => $middleNameAddress, 'middleNameContactAddress' => $middleNameAddress,
                    'postalCodeAddress' => $postalCodeAddress, 'postalCodeContactAddress' => $postalCodeAddress,
                    'prefixAddress' => $prefixAddress, 'prefixContactAddress' => $prefixAddress,
                    'regionAddress' => $regionAddress, 'regionContactAddress' => $regionAddress,
                    'regionTextAddress' => $regionTextAddress, 'regionTextContactAddress' => $regionTextAddress,
                    'streetAddress' => $streetAddress, 'streetContactAddress' => $streetAddress,
                    'nameSuffixAddress' => $nameSuffixAddress,
                    'nameSuffixContactAddress' => $nameSuffixAddress,
                ],
                (object)[
                    'object' =>[
                        'email' => $email,
                        'lastname' => $lastName,
                        'suffix' => $suffix,
                        'gender' => $gender,
                        'addresses' => [
                            'city' => $cityAddress,
                            'company' => $organizationAddress,
                        ]
                    ],
                    'id' => $id,
                    'originId' => $originId,
                ]
            ],
            [
                'full entry' =>
                    [
                        'id' => $id, 'originId' => $originId,
                        'email' => 'e1@e.com', 'emailContact' => $email,
                        'firstName' => 'jane', 'firstNameContact'=> $firstName,
                        'lastName' => 'Smith', 'lastNameContact' => $lastName,
                        'prefix' => 'prefix', 'prefixContact' => $prefix,
                        'suffix' => '', 'suffixContact' => $suffix,
                        'dob' => '01.02.1975', 'dobContact' => $dob,
                        'gender' => 'S', 'genderContact' => $gender,
                        'middleName' => 'middle', 'middleNameContact' => $middleName,
                        'cityAddress' => '', 'cityContactAddress' => $cityAddress,
                        'organizationAddress' => '', 'organizationContactAddress' => $organizationAddress,
                        'countryAddress' => '', 'countryContactAddress' => $countryAddress,
                        'firstNameAddress' => '', 'firstNameContactAddress' => $firstNameAddress,
                        'lastNameAddress' => '', 'lastNameContactAddress' => $lastNameAddress,
                        'middleNameAddress' => 'middle', 'middleNameContactAddress' => $middleNameAddress,
                        'postalCodeAddress' => '12458', 'postalCodeContactAddress' => $postalCodeAddress,
                        'prefixAddress' => 'pref1', 'prefixContactAddress' => $prefixAddress,
                        'regionAddress' => 'reg', 'regionContactAddress' => $regionAddress,
                        'regionTextAddress' => 'retext', 'regionTextContactAddress' => $regionTextAddress,
                        'streetAddress' => 'str', 'streetContactAddress' => $streetAddress,
                        'nameSuffixAddress' => 'suf',
                        'nameSuffixContactAddress' => $nameSuffixAddress,
                    ],
                (object)[
                    'object' =>[
                        'email' => $email,
                        'firstname' => $firstName,
                        'lastname' => $lastName,
                        'prefix' => $prefix,
                        'suffix' => $suffix,
                        'dob' => $dob,
                        'gender' => $gender,
                        'middlename' => $middleName,
                        'addresses' => [
                            'city' => $cityAddress,
                            'company' => $organizationAddress,
                            'country_id' => $countryAddress,
                            'firstname' => $firstNameAddress,
                            'lastname' => $lastNameAddress,
                            'middlename' => $middleNameAddress,
                            'postcode' => $postalCodeAddress,
                            'prefix' => $prefixAddress,
                            'region_id' => $regionAddress,
                            'region' => $regionTextAddress,
                            'street' => $streetAddress,
                            'suffix' => $nameSuffixAddress,
                        ]
                    ],
                    'id' => $id,
                    'originId' => $originId,
                ]
            ],
            [
                'nothing to change' =>
                    [
                        'id' => $id, 'originId' => $originId,
                        'email' => $email, 'emailContact' => $email,
                        'firstName' => $firstName, 'firstNameContact'=> $firstName,
                        'lastName' => $lastName, 'lastNameContact' => $lastName,
                        'prefix' => $prefix, 'prefixContact' => $prefix,
                        'suffix' => $suffix, 'suffixContact' => $suffix,
                        'dob' => $dob, 'dobContact' => $dob,
                        'gender' => $gender, 'genderContact' => $gender,
                        'middleName' => $middleName, 'middleNameContact' => $middleName,
                        'cityAddress' => $cityAddress, 'cityContactAddress' => $cityAddress,
                        'organizationAddress' => $organizationAddress,
                        'organizationContactAddress' => $organizationAddress,
                        'countryAddress' => $countryAddress, 'countryContactAddress' => $countryAddress,
                        'firstNameAddress' => $firstNameAddress, 'firstNameContactAddress' => $firstNameAddress,
                        'lastNameAddress' => $lastNameAddress, 'lastNameContactAddress' => $lastNameAddress,
                        'middleNameAddress' => $middleNameAddress, 'middleNameContactAddress' => $middleNameAddress,
                        'postalCodeAddress' => $postalCodeAddress, 'postalCodeContactAddress' => $postalCodeAddress,
                        'prefixAddress' => $prefixAddress, 'prefixContactAddress' => $prefixAddress,
                        'regionAddress' => $regionAddress, 'regionContactAddress' => $regionAddress,
                        'regionTextAddress' => $regionTextAddress, 'regionTextContactAddress' => $regionTextAddress,
                        'streetAddress' => $streetAddress, 'streetContactAddress' => $streetAddress,
                        'nameSuffixAddress' => $nameSuffixAddress,
                        'nameSuffixContactAddress' => $nameSuffixAddress,
                    ],
                (object)['object' => [],'id' => $id, 'originId' => $originId]
            ],
            [
                'no originId' =>
                    [
                        'id' => $id,
                        'email' => $email, 'emailContact' => $email,
                        'firstName' => $firstName, 'firstNameContact'=> $firstName,
                        'lastName' => $lastName, 'lastNameContact' => $lastName,
                        'prefix' => $prefix, 'prefixContact' => $prefix,
                        'suffix' => $suffix, 'suffixContact' => $suffix,
                        'dob' => $dob, 'dobContact' => $dob,
                        'gender' => $gender, 'genderContact' => $gender,
                        'middleName' => $middleName, 'middleNameContact' => $middleName,
                        'cityAddress' => $cityAddress, 'cityContactAddress' => $cityAddress,
                        'organizationAddress' => $organizationAddress,
                        'organizationContactAddress' => $organizationAddress,
                        'countryAddress' => $countryAddress, 'countryContactAddress' => $countryAddress,
                        'firstNameAddress' => $firstNameAddress, 'firstNameContactAddress' => $firstNameAddress,
                        'lastNameAddress' => $lastNameAddress, 'lastNameContactAddress' => $lastNameAddress,
                        'middleNameAddress' => $middleNameAddress, 'middleNameContactAddress' => $middleNameAddress,
                        'postalCodeAddress' => $postalCodeAddress, 'postalCodeContactAddress' => $postalCodeAddress,
                        'prefixAddress' => $prefixAddress, 'prefixContactAddress' => $prefixAddress,
                        'regionAddress' => $regionAddress, 'regionContactAddress' => $regionAddress,
                        'regionTextAddress' => $regionTextAddress, 'regionTextContactAddress' => $regionTextAddress,
                        'streetAddress' => $streetAddress, 'streetContactAddress' => $streetAddress,
                        'nameSuffixAddress' => $nameSuffixAddress,
                        'nameSuffixContactAddress' => $nameSuffixAddress,
                    ],
                (object)['object' => [],'id' => $id]
            ],
        ];

    }

    /**
     * @dataProvider  getDataProvider
     *
     * @param array $fields
     * @param \stdClass $finO
     */
    public function testProcess(array $fields, $finO)
    {
        $customerReverseProcessor = new CustomerReverseProcessor();

        if (!empty($finO->object)) {
            $finO->channel = $this->channel;
        }

        $this->customer->expects($this->any())->method('getId')
            ->will($this->returnValue($fields['id']));

        if (!empty($fields['originId'])) {
            $this->customer->expects($this->any())->method('getOriginId')
                ->will($this->returnValue($fields['originId']));
        }

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

        $this->assertEquals(
            $finO,
            $customerReverseProcessor->process($this->customer)
        );
    }
}
