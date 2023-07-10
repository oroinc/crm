<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;

class LeadAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testOwner(): void
    {
        $lead = new Lead();
        $address = new LeadAddress();

        $address->setOwner($lead);
        self::assertSame($lead, $address->getOwner());
        self::assertCount(1, $lead->getAddresses());
        self::assertSame($address, $lead->getAddresses()->first());

        $address->setOwner($lead);
        self::assertSame($lead, $address->getOwner());
        self::assertCount(1, $lead->getAddresses());
        self::assertSame($address, $lead->getAddresses()->first());

        $address->setOwner(null);
        self::assertNull($address->getOwner());
        self::assertCount(0, $lead->getAddresses());
    }

    public function testPrimary(): void
    {
        $address = new LeadAddress();
        $this->assertFalse($address->isPrimary());

        $address->setPrimary(true);

        $this->assertTrue($address->isPrimary());
    }
}
