<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\MagentoBundle\Provider\CartAddressPhoneProvider;

class CartAddressPhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartAddressPhoneProvider */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new CartAddressPhoneProvider();
    }

    public function testGetPhoneNumber()
    {
        $entity = new CartAddress();

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $entity->setPhone('123-123');
        $this->assertEquals(
            '123-123',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity = new CartAddress();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );

        $entity->setPhone('123-123');
        $this->assertEquals(
            [
                ['123-123', $entity]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
