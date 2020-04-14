<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Provider\AddressPhoneProvider;

class AddressPhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressPhoneProvider */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new AddressPhoneProvider();
    }

    public function testGetPhoneNumber()
    {
        $entity = new Address();

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $entity->setContactPhone(new ContactPhone('123-123'));
        $this->assertEquals(
            '123-123',
            $this->provider->getPhoneNumber($entity)
        );

        $entity->setPhone('456-456');
        $this->assertEquals(
            '456-456',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity  = new Address();
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );

        $contactPhone = new ContactPhone('123-123');
        $contactPhone->setOwner($contact);
        $entity->setContactPhone($contactPhone);
        $this->assertSame(
            [
                ['123-123', $contact]
            ],
            $this->provider->getPhoneNumbers($entity)
        );

        $entity->setPhone('456-456');
        $this->assertSame(
            [
                ['456-456', $entity],
                ['123-123', $contact],
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
