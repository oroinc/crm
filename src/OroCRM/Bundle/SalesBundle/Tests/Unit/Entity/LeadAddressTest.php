<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\LeadAddress;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

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
