<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Converter;

use OroCRM\Bundle\ContactBundle\ImportExport\Converter\ContactDataConverter;

class ContactDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactDataConverter
     */
    protected $dataConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $headerProvider;

    /**
     * @var array
     */
    protected $backendHeader = array(
        'id',
        'namePrefix',
        'firstName',
        'middleName',
        'lastName',
        'nameSuffix',
        'gender',
        'description',
        'jobTitle',
        'fax',
        'skype',
        'twitter',
        'facebook',
        'googlePlus',
        'linkedIn',
        'birthday',
        'source',
        'method',
        'owner:username',
        'owner:fullName',
        'assignedTo:username',
        'assignedTo:fullName',
        'addresses:0:label',
        'addresses:0:firstName',
        'addresses:0:lastName',
        'addresses:0:street',
        'addresses:0:street2',
        'addresses:0:city',
        'addresses:0:region',
        'addresses:0:regionText',
        'addresses:0:country',
        'addresses:0:postalCode',
        'addresses:0:types:0',
        'addresses:0:types:1',
        'addresses:1:label',
        'addresses:1:firstName',
        'addresses:1:lastName',
        'addresses:1:street',
        'addresses:1:street2',
        'addresses:1:city',
        'addresses:1:region',
        'addresses:1:regionText',
        'addresses:1:country',
        'addresses:1:postalCode',
        'addresses:1:types:0',
        'addresses:1:types:1',
        'emails:0',
        'emails:1',
        'phones:0',
        'phones:1',
        'groups:0',
        'groups:1',
        'accounts:0',
        'accounts:1',
    );

    protected function setUp()
    {
        $this->headerProvider
            = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactHeaderProvider')
                ->disableOriginalConstructor()
                ->setMethods(array('getHeader', 'setQueryBuilder'))
                ->getMock();
        $this->headerProvider->expects($this->any())
            ->method('getHeader')
            ->will($this->returnValue($this->backendHeader));

        $this->dataConverter = new ContactDataConverter($this->headerProvider);
    }

    protected function tearDown()
    {
        unset($this->dataConverter);
    }

    /**
     * @param array $exportedRecord
     * @param array $result
     * @dataProvider convertToExportFormatDataProvider
     */
    public function testConvertToExportFormat(array $exportedRecord, array $result)
    {
        $this->assertEquals($result, $this->dataConverter->convertToExportFormat($exportedRecord));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function convertToExportFormatDataProvider()
    {
        return array(
            'minimal data' => array(
                'exportedRecord' => array(
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                ),
                'result' => array(
                    'ID' => '',
                    'Name Prefix' => '',
                    'First Name' => 'John',
                    'Middle Name' => '',
                    'Last Name' => 'Doe',
                    'Name Suffix' => '',
                    'Gender' => '',
                    'Description' => '',
                    'Job Title' => '',
                    'Fax' => '',
                    'Skype' => '',
                    'Twitter' => '',
                    'Facebook' => '',
                    'GooglePlus' => '',
                    'LinkedIn' => '',
                    'Birthday' => '',
                    'Source' => '',
                    'Method' => '',
                    'Owner Username' => '',
                    'Owner' => '',
                    'Assigned To Username' => '',
                    'Assigned To' => '',
                    'Primary Address Label' => '',
                    'Primary Address First Name' => '',
                    'Primary Address Last Name' => '',
                    'Primary Address Street' => '',
                    'Primary Address Street2' => '',
                    'Primary Address City' => '',
                    'Primary Address Region' => '',
                    'Primary Address Region Text' => '',
                    'Primary Address Country' => '',
                    'Primary Address Postal Code' => '',
                    'Primary Address Type 1' => '',
                    'Primary Address Type 2' => '',
                    'Address 1 Label' => '',
                    'Address 1 First Name' => '',
                    'Address 1 Last Name' => '',
                    'Address 1 Street' => '',
                    'Address 1 Street2' => '',
                    'Address 1 City' => '',
                    'Address 1 Region' => '',
                    'Address 1 Region Text' => '',
                    'Address 1 Country' => '',
                    'Address 1 Postal Code' => '',
                    'Address 1 Type 1' => '',
                    'Address 1 Type 2' => '',
                    'Primary Email' => '',
                    'Email 1' => '',
                    'Primary Phone' => '',
                    'Phone 1' => '',
                    'Group 1' => '',
                    'Group 2' => '',
                    'Account 1' => '',
                    'Account 2' => '',
                )
            ),
            'full data' => array(
                'exportedRecord' => array(
                    'id' => 69,
                    'namePrefix' => 'Mr.',
                    'firstName' => 'John',
                    'middleName' => 'Middle',
                    'lastName' => 'Doe',
                    'nameSuffix' => 'Jr.',
                    'gender' => 'male',
                    'description' => 'some person',
                    'jobTitle' => 'Engineer',
                    'fax' => '444',
                    'skype' => 'john.doe',
                    'twitter' => 'john.doe.twitter',
                    'facebook' => 'john.doe.facebook',
                    'googlePlus' => 'john.doe.googlePlus',
                    'linkedIn' => 'john.doe.linkedIn',
                    'birthday' => '1944-08-29T16:52:09+0200',
                    'source' => 'tv',
                    'method' => 'email',
                    'owner' => array(
                        'username' => 'w.stewart',
                        'fullName' => 'William Stewart',
                    ),
                    'assignedTo' => array(
                        'username' => 'w.stewart',
                        'fullName' => 'William Stewart',
                    ),
                    'addresses' => array(
                        array(
                            'label' => 'Billing Address',
                            'firstName' => 'John',
                            'lastName' => 'Doe',
                            'street' => 'First Street',
                            'street2' => null,
                            'city' => 'London',
                            'regionText' => null,
                            'region' => 'ENG',
                            'country' => 'UK',
                            'postalCode' => '555666777',
                            'types' => array('billing')
                        ),
                        array(
                            'label' => 'Shipping Address',
                            'firstName' => 'Jane',
                            'lastName' => 'Smith',
                            'street' => 'Second street',
                            'street2' => '2nd',
                            'city' => 'Small Town',
                            'region' => null,
                            'regionText' => 'Small Region',
                            'country' => 'UK',
                            'postalCode' => '777888999',
                            'types' => array('shipping')
                        ),
                    ),
                    'emails' => array(
                        'john@example.com',
                        'doe@example.com',
                    ),
                    'phones' => array(
                        '0 800 11 22 444',
                        '0 800 11 22 555',
                    ),
                    'groups' => array(
                        'first_group',
                        'second_group',
                    ),
                    'accounts' => array(
                        'First Company',
                        'Second Company',
                    )
                ),
                'result' => array (
                    'ID' => '69',
                    'Name Prefix' => 'Mr.',
                    'First Name' => 'John',
                    'Middle Name' => 'Middle',
                    'Last Name' => 'Doe',
                    'Name Suffix' => 'Jr.',
                    'Gender' => 'male',
                    'Description' => 'some person',
                    'Job Title' => 'Engineer',
                    'Fax' => '444',
                    'Skype' => 'john.doe',
                    'Twitter' => 'john.doe.twitter',
                    'Facebook' => 'john.doe.facebook',
                    'GooglePlus' => 'john.doe.googlePlus',
                    'LinkedIn' => 'john.doe.linkedIn',
                    'Birthday' => '1944-08-29T16:52:09+0200',
                    'Source' => 'tv',
                    'Method' => 'email',
                    'Owner Username' => 'w.stewart',
                    'Owner' => 'William Stewart',
                    'Assigned To Username' => 'w.stewart',
                    'Assigned To' => 'William Stewart',
                    'Primary Address Label' => 'Billing Address',
                    'Primary Address First Name' => 'John',
                    'Primary Address Last Name' => 'Doe',
                    'Primary Address Street' => 'First Street',
                    'Primary Address Street2' => '',
                    'Primary Address City' => 'London',
                    'Primary Address Region' => 'ENG',
                    'Primary Address Region Text' => '',
                    'Primary Address Country' => 'UK',
                    'Primary Address Postal Code' => '555666777',
                    'Primary Address Type 1' => 'billing',
                    'Primary Address Type 2' => '',
                    'Address 1 Label' => 'Shipping Address',
                    'Address 1 First Name' => 'Jane',
                    'Address 1 Last Name' => 'Smith',
                    'Address 1 Street' => 'Second street',
                    'Address 1 Street2' => '2nd',
                    'Address 1 City' => 'Small Town',
                    'Address 1 Region' => '',
                    'Address 1 Region Text' => 'Small Region',
                    'Address 1 Country' => 'UK',
                    'Address 1 Postal Code' => '777888999',
                    'Address 1 Type 1' => 'shipping',
                    'Address 1 Type 2' => '',
                    'Primary Email' => 'john@example.com',
                    'Email 1' => 'doe@example.com',
                    'Primary Phone' => '0 800 11 22 444',
                    'Phone 1' => '0 800 11 22 555',
                    'Group 1' => 'first_group',
                    'Group 2' => 'second_group',
                    'Account 1' => 'First Company',
                    'Account 2' => 'Second Company',
                )
            ),
        );
    }

    /**
     * @param array $importedRecord
     * @param array $result
     * @dataProvider convertToImportFormatDataProvider
     */
    public function testConvertToImportFormat(array $importedRecord, array $result)
    {
        $this->assertEquals($result, $this->dataConverter->convertToImportFormat($importedRecord));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function convertToImportFormatDataProvider()
    {
        return array(
            'minimal data' => array(
                'importedRecord' => array(
                    'First Name' => 'John',
                    'Last Name'  => 'Doe',
                ),
                'result' => array(
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                )
            ),
            'full data' => array(
                'importedRecord' => array(
                    'ID' => '69',
                    'Name Prefix' => 'Mr.',
                    'First Name' => 'John',
                    'Last Name' => 'Doe',
                    'Name Suffix' => 'Jr.',
                    'Gender' => 'male',
                    'Description' => 'some person',
                    'Job Title' => 'Engineer',
                    'Fax' => '444',
                    'Skype' => 'john.doe',
                    'Twitter' => 'john.doe.twitter',
                    'Facebook' => 'john.doe.facebook',
                    'GooglePlus' => 'john.doe.googlePlus',
                    'LinkedIn' => 'john.doe.linkedIn',
                    'Birthday' => '1944-08-29T16:52:09+0200',
                    'Source' => 'tv',
                    'Method' => 'email',
                    'Owner Username' => 'w.stewart',
                    'Owner' => 'William Stewart',
                    'Assigned To Username' => 'w.stewart',
                    'Assigned To' => 'William Stewart',
                    'Primary Address Label' => 'Billing Address',
                    'Primary Address First Name' => 'John',
                    'Primary Address Last Name' => 'Doe',
                    'Primary Address Street' => 'First Street',
                    'Primary Address Street2' => '',
                    'Primary Address City' => 'London',
                    'Primary Address Region' => 'ENG',
                    'Primary Address Region Text' => '',
                    'Primary Address Country' => 'UK',
                    'Primary Address Postal Code' => '555666777',
                    'Primary Address Type 1' => 'billing',
                    'Primary Address Type 2' => '',
                    'Address 1 Label' => 'Shipping Address',
                    'Address 1 First Name' => 'Jane',
                    'Address 1 Last Name' => 'Smith',
                    'Address 1 Street' => 'Second street',
                    'Address 1 Street2' => '2nd',
                    'Address 1 City' => 'Small Town',
                    'Address 1 Region' => '',
                    'Address 1 Region Text' => 'Small Region',
                    'Address 1 Country' => 'UK',
                    'Address 1 Postal Code' => '777888999',
                    'Address 1 Type 1' => 'shipping',
                    'Address 1 Type 2' => '',
                    'Primary Email' => 'john@example.com',
                    'Email 1' => 'doe@example.com',
                    'Primary Phone' => '0 800 11 22 444',
                    'Phone 1' => '0 800 11 22 555',
                    'Group 1' => 'first_group',
                    'Group 2' => 'second_group',
                    'Account 1' => 'First Company',
                    'Account 2' => 'Second Company',
                ),
                'result' => array(
                    'id' => 69,
                    'namePrefix' => 'Mr.',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'nameSuffix' => 'Jr.',
                    'gender' => 'male',
                    'description' => 'some person',
                    'jobTitle' => 'Engineer',
                    'fax' => '444',
                    'skype' => 'john.doe',
                    'twitter' => 'john.doe.twitter',
                    'facebook' => 'john.doe.facebook',
                    'googlePlus' => 'john.doe.googlePlus',
                    'linkedIn' => 'john.doe.linkedIn',
                    'birthday' => '1944-08-29T16:52:09+0200',
                    'source' => 'tv',
                    'method' => 'email',
                    'owner' => array(
                        'username' => 'w.stewart',
                        'fullName' => 'William Stewart',
                    ),
                    'assignedTo' => array(
                        'username' => 'w.stewart',
                        'fullName' => 'William Stewart',
                    ),
                    'addresses' => array(
                        array(
                            'label' => 'Billing Address',
                            'firstName' => 'John',
                            'lastName' => 'Doe',
                            'street' => 'First Street',
                            'city' => 'London',
                            'region' => 'ENG',
                            'country' => 'UK',
                            'postalCode' => '555666777',
                            'types' => array('billing')
                        ),
                        array(
                            'label' => 'Shipping Address',
                            'firstName' => 'Jane',
                            'lastName' => 'Smith',
                            'street' => 'Second street',
                            'street2' => '2nd',
                            'city' => 'Small Town',
                            'regionText' => 'Small Region',
                            'country' => 'UK',
                            'postalCode' => '777888999',
                            'types' => array('shipping')
                        ),
                    ),
                    'emails' => array(
                        'john@example.com',
                        'doe@example.com',
                    ),
                    'phones' => array(
                        '0 800 11 22 444',
                        '0 800 11 22 555',
                    ),
                    'groups' => array(
                        'first_group',
                        'second_group',
                    ),
                    'accounts' => array(
                        'First Company',
                        'Second Company',
                    )
                )
            ),
        );
    }

    public function testSetQueryBuilder()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->headerProvider->expects($this->once())
            ->method('setQueryBuilder')
            ->with($queryBuilder);

        $this->dataConverter->setQueryBuilder($queryBuilder);
    }
}
