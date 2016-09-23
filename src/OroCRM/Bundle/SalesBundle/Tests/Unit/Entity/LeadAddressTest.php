<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\Lead;

class LeadAddressTest extends \PHPUnit_Framework_TestCase
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
