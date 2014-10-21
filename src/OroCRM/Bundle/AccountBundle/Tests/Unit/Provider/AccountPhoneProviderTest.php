<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\Provider;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\AccountBundle\Provider\AccountPhoneProvider;

class AccountPhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $rootProvider;

    /** @var AccountPhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->rootProvider = $this->getMock('Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface');
        $this->provider = new AccountPhoneProvider();
        $this->provider->setRootProvider($this->rootProvider);
    }

    public function testGetPhoneNumberNoContact()
    {
        $entity = new Account();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumber');

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumber()
    {
        $entity = new Account();
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->setDefaultContact($contact);
        $this->rootProvider->expects($this->once())
            ->method('getPhoneNumber')
            ->with($this->identicalTo($contact))
            ->will($this->returnValue('123-123'));

        $this->assertEquals(
            '123-123',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbersNoContact()
    {
        $entity = new Account();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumbers');

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity = new Account();
        $contact1 = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();
        $contact2 = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->setDefaultContact($contact1);
        $entity->addContact($contact1);
        $entity->addContact($contact2);

        $this->rootProvider->expects($this->at(0))
            ->method('getPhoneNumbers')
            ->with($this->identicalTo($contact1))
            ->will(
                $this->returnValue(
                    [
                        ['123-123', $contact1],
                        ['456-456', $contact1]
                    ]
                )
            );
        $this->rootProvider->expects($this->at(1))
            ->method('getPhoneNumbers')
            ->with($this->identicalTo($contact2))
            ->will(
                $this->returnValue(
                    [
                        ['789-789', $contact2],
                        ['111-111', $contact2]
                    ]
                )
            );

        $this->assertEquals(
            [
                ['123-123', $contact1],
                ['456-456', $contact1],
                ['789-789', $contact2],
                ['111-111', $contact2]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
