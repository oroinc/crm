<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class CartAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPrimaryPhoneNumber()
    {
        $address = new CartAddress();

        $this->assertNull($address->getPrimaryPhoneNumber());

        $address->setPhone('123-123');
        $this->assertEquals('123-123', $address->getPrimaryPhoneNumber());
    }

    public function testGetPhoneNumbers()
    {
        $address = new CartAddress();

        $this->assertSame([], $address->getPhoneNumbers());

        $address->setPhone('123-123');
        $this->assertSame(['123-123'], $address->getPhoneNumbers());
    }
}
