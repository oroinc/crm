<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Provider\OrderAddressPhoneProvider;

class OrderAddressPhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrderAddressPhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new OrderAddressPhoneProvider();
    }

    public function testGetPhoneNumber()
    {
        $entity = new OrderAddress();

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
        $entity = new OrderAddress();

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
