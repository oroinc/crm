<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Provider;

use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Provider\CasePhoneProvider;

class CasePhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $rootProvider;

    /** @var CasePhoneProvider */
    protected $provider;

    protected function setUp(): void
    {
        $this->rootProvider = $this->createMock('Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface');
        $this->provider     = new CasePhoneProvider();
        $this->provider->setRootProvider($this->rootProvider);
    }

    public function testGetPhoneNumberNoContact()
    {
        $entity = new CaseEntity();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumber');

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumber()
    {
        $entity  = new CaseEntity();
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->setRelatedContact($contact);
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
        $entity = new CaseEntity();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumbers');

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity  = new CaseEntity();
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $entity->setRelatedContact($contact);
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
