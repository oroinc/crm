<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\OpportunityPhoneProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OpportunityPhoneProviderTest extends TestCase
{
    private PhoneProviderInterface&MockObject $rootProvider;
    private OpportunityPhoneProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->rootProvider = $this->createMock(PhoneProviderInterface::class);

        $this->provider = new OpportunityPhoneProvider();
        $this->provider->setRootProvider($this->rootProvider);
    }

    public function testGetPhoneNumberNoContact(): void
    {
        $entity = new Opportunity();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumber');

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumber(): void
    {
        $entity = new Opportunity();
        $contact = $this->createMock(Contact::class);

        $entity->setContact($contact);
        $this->rootProvider->expects($this->once())
            ->method('getPhoneNumber')
            ->with($this->identicalTo($contact))
            ->willReturn('123-123');

        $this->assertEquals(
            '123-123',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbersNoContact(): void
    {
        $entity = new Opportunity();

        $this->rootProvider->expects($this->never())
            ->method('getPhoneNumbers');

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );
    }

    public function testGetPhoneNumbers(): void
    {
        $entity = new Opportunity();
        $contact = $this->createMock(Contact::class);

        $entity->setContact($contact);
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
