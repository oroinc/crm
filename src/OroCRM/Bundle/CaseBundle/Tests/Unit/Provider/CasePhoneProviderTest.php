<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Provider;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;
use OroCRM\Bundle\CaseBundle\Provider\CasePhoneProvider;

class CasePhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $rootProvider;

    /** @var CasePhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->rootProvider = $this->getMock('Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface');
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
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
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
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
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
