<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Provider\CasePhoneProvider;
use Oro\Bundle\ContactBundle\Entity\Contact;

class CasePhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var PhoneProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $rootProvider;

    /** @var CasePhoneProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->rootProvider = $this->createMock(PhoneProviderInterface::class);

        $this->provider = new CasePhoneProvider();
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
        $entity = new CaseEntity();
        $contact = $this->createMock(Contact::class);

        $entity->setRelatedContact($contact);
        $this->rootProvider->expects($this->once())
            ->method('getPhoneNumber')
            ->with($this->identicalTo($contact))
            ->willReturn('123-123');

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
        $entity = new CaseEntity();
        $contact = $this->createMock(Contact::class);

        $entity->setRelatedContact($contact);
        $this->rootProvider->expects($this->once())
            ->method('getPhoneNumbers')
            ->with($this->identicalTo($contact))
            ->willReturn(
                [
                    ['123-123', $contact],
                    ['456-456', $contact]
                ]
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
