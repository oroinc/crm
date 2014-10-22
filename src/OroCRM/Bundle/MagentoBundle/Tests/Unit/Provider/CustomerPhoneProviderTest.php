<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerPhoneProvider;

class CustomerPhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $rootProvider;

    /** @var CustomerPhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->rootProvider = $this->getMock('Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface');
        $this->provider     = new CustomerPhoneProvider();
        $this->provider->setRootProvider($this->rootProvider);
    }

    public function testGetPhoneNumberNoContact()
    {
        $entity = new Customer();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumber');

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumber()
    {
        $entity  = new Customer();
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->setContact($contact);
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
        $entity = new Customer();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumbers');

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity  = new Customer();
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->setContact($contact);
        $this->rootProvider->expects($this->once())
            ->method('getPhoneNumbers')
            ->with($this->identicalTo($contact))
            ->will(
                $this->returnValue(
                    [
                        ['123-123', $contact],
                        ['456-456', $contact]
                    ]
                )
            );

        $this->assertEquals(
            [
                ['123-123', $contact],
                ['456-456', $contact]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
