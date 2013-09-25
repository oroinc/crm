<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactHeaderProvider;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactMaxDataProvider;

class ContactHeaderProviderTest extends \PHPUnit_Framework_TestCase
{
    const MAX_ACCOUNTS      = 2;
    const MAX_ADDRESSES     = 3;
    const MAX_EMAILS        = 4;
    const MAX_PHONES        = 5;
    const MAX_GROUPS        = 6;
    const MAX_ADDRESS_TYPES = 1;

    /**
     * @var array
     */
    protected $serializedContact = array(
        'firstName' => 'John',
        'lastName'  => 'Doe',
        'emails' => array(
            'john@qwerty.com',
            'doe@qwerty.com',
        )
    );

    /**
     * @var array
     */
    protected $plainContactData = array(
        'firstName' => 'John',
        'lastName'  => 'Doe',
        'emails:0'  => 'john@qwerty.com',
        'emails:1'  => 'doe@qwerty.com',
    );

    /**
     * @var array
     */
    protected $addressTypes = array(
        AddressType::TYPE_BILLING,
        AddressType::TYPE_SHIPPING
    );

    public function testGetHeader()
    {
        $test = $this;
        $serializedContact = $this->serializedContact;

        // used to receive max counts for contact related entities
        $contactMaxDataProvider = $this->getContactMaxDataProviderMock();

        $serializer = $this->getMockBuilder('Symfony\Component\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serializer->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Contact'), null)
            ->will(
                $this->returnCallback(
                    function ($contact, $format) use ($test, $serializedContact) {
                        $test->assertMaxContactEntity($contact);
                        $test->assertNull($format);
                        return $serializedContact;
                    }
                )
            );

        $dataConverter = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $dataConverter->expects($this->once())
            ->method('convertToExportFormat')
            ->with($this->serializedContact)
            ->will($this->returnValue($this->plainContactData));

        // test
        $headerProvider = new ContactHeaderProvider(
            $serializer,
            $dataConverter,
            $contactMaxDataProvider
        );
        $expectedHeader = array_keys($this->plainContactData);

        // header is stored in internal cache, all functional methods must be called only once
        $this->assertEquals($expectedHeader, $headerProvider->getHeader());
        $this->assertEquals($expectedHeader, $headerProvider->getHeader());
        $this->assertAttributeEquals($expectedHeader, 'maxHeader', $headerProvider);
    }

    /**
     * @param Contact $contact
     */
    public function assertMaxContactEntity(Contact $contact)
    {
        $this->assertCount(self::MAX_ACCOUNTS, $contact->getAccounts());
        $this->assertCount(self::MAX_ADDRESSES, $contact->getAddresses());
        $this->assertCount(self::MAX_EMAILS, $contact->getEmails());
        $this->assertCount(self::MAX_PHONES, $contact->getPhones());
        $this->assertCount(self::MAX_GROUPS, $contact->getGroups());

        /** @var ContactAddress $address */
        foreach ($contact->getAddresses() as $address) {
            $this->assertCount(self::MAX_ADDRESS_TYPES, $address->getTypes());
        }
    }

    /**
     * @return ContactMaxDataProvider
     */
    protected function getContactMaxDataProviderMock()
    {
        $expectedMethods = array(
            'getMaxAccountsCount'     => self::MAX_ACCOUNTS,
            'getMaxAddressesCount'    => self::MAX_ADDRESSES,
            'getMaxEmailsCount'       => self::MAX_EMAILS,
            'getMaxPhonesCount'       => self::MAX_PHONES,
            'getMaxGroupsCount'       => self::MAX_GROUPS,
            'getMaxAddressTypesCount' => self::MAX_ADDRESS_TYPES,
        );

        $provider = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactMaxDataProvider')
            ->disableOriginalConstructor()
            ->setMethods(array_keys($expectedMethods))
            ->getMock();

        foreach ($expectedMethods as $methodName => $expectedCount) {
            $provider->expects($this->once())
                ->method($methodName)
                ->will($this->returnValue($expectedCount));
        }

        return $provider;
    }

    /**
     * @return ManagerRegistry
     */
    protected function getManagerRegistryMock()
    {
        $addressTypes = array();
        foreach ($this->addressTypes as $type) {
            $addressTypes[] = new AddressType($type);
        }

        $addressTypesRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findAll'))
            ->getMock();
        $addressTypesRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($addressTypes));

        $managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $managerRegistry->expects($this->once())
            ->method('getRepository')
            ->with('OroAddressBundle:AddressType')
            ->will($this->returnValue($addressTypesRepository));

        return $managerRegistry;
    }

    public function testSetQueryBuilder()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $managerRegistry = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ManagerRegistry');
        $serializer = $this->getMockForAbstractClass('Symfony\Component\Serializer\SerializerInterface');
        $dataConverter
            = $this->getMockForAbstractClass('Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface');

        $contactMaxDataProvider
            = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactMaxDataProvider')
                ->disableOriginalConstructor()
                ->setMethods(array('setQueryBuilder'))
                ->getMock();
        $contactMaxDataProvider->expects($this->once())
            ->method('setQueryBuilder')
            ->with($queryBuilder);

        $headerProvider = new ContactHeaderProvider(
            $serializer,
            $dataConverter,
            $contactMaxDataProvider
        );
        $headerProvider->setQueryBuilder($queryBuilder);
    }
}
