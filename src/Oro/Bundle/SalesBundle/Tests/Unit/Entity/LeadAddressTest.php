<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;

class LeadAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testOwner()
    {
        $lead = new Lead();
        $address = new LeadAddress();
        $address->setOwner($lead);
        $this->assertSame($lead, $address->getOwner());
    }

    public function testPrimary()
    {
        $address = new LeadAddress();
        $this->assertFalse($address->isPrimary());

        $address->setPrimary(true);

        $this->assertTrue($address->isPrimary());
    }
}
