<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Provider\AccountPhoneProvider;
use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;

class AccountPhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var PhoneProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $rootProvider;

    /** @var AccountPhoneProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->rootProvider = $this->createMock(PhoneProviderInterface::class);

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
        $contact = new Contact();
        $entity->setDefaultContact($contact);

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
        $contact1 = new Contact();
        $contact2 = new Contact();
        $entity->addContact($contact1);
        $entity->addContact($contact2);
        $entity->setDefaultContact($contact1);

        $this->rootProvider->expects($this->exactly(2))
            ->method('getPhoneNumbers')
            ->withConsecutive(
                [$this->identicalTo($contact1)],
                [$this->identicalTo($contact2)]
            )
            ->willReturnOnConsecutiveCalls(
                [
                    ['123-123', $contact1],
                    ['456-456', $contact1]
                ],
                [
                    ['789-789', $contact2],
                    ['111-111', $contact2]
                ]
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
