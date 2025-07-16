<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use Oro\Bundle\SalesBundle\Provider\B2bCustomerPhoneProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class B2bCustomerPhoneProviderTest extends TestCase
{
    private PhoneProviderInterface&MockObject $rootProvider;
    private B2bCustomerPhoneProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->rootProvider = $this->createMock(PhoneProviderInterface::class);

        $this->provider = new B2bCustomerPhoneProvider();
        $this->provider->setRootProvider($this->rootProvider);
    }

    public function testGetPhoneNumber(): void
    {
        $entity = new B2bCustomer();
        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $phone1 = new B2bCustomerPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new B2bCustomerPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);
        $this->assertEquals(
            '456-456',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers(): void
    {
        $entity = new B2bCustomer();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );
        $phone1 = new B2bCustomerPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new B2bCustomerPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);
        $this->assertSame(
            [
                ['123-123', $entity],
                ['456-456', $entity]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
