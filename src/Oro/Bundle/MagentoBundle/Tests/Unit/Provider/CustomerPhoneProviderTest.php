<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\CustomerPhoneProvider;

class CustomerPhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $rootProvider;

    /** @var CustomerPhoneProvider */
    protected $provider;

    protected function setUp(): void
    {
        $this->rootProvider = $this->createMock('Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface');
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
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
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
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
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
