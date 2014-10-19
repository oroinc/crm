<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPhoneNumber()
    {
        $address = new OrderAddress();

        $this->assertNull($address->getPhoneNumber());

        $address->setPhone('123-123');
        $this->assertEquals('123-123', $address->getPhoneNumber());
    }

    public function testGetPhoneNumbers()
    {
        $address = new OrderAddress();

        $this->assertSame([], $address->getPhoneNumbers());

        $address->setPhone('123-123');
        $this->assertSame(['123-123'], $address->getPhoneNumbers());
    }
}
