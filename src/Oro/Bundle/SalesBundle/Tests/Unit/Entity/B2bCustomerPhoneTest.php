<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use PHPUnit\Framework\TestCase;

class B2bCustomerPhoneTest extends TestCase
{
    private B2bCustomerPhone $phone;

    #[\Override]
    protected function setUp(): void
    {
        $this->phone = new B2bCustomerPhone();
    }

    public function testOwner(): void
    {
        $this->assertNull($this->phone->getOwner());

        $customer = new B2bCustomer();
        $this->phone->setOwner($customer);

        $this->assertEquals($customer, $this->phone->getOwner());
        $this->assertContains($this->phone, $customer->getPhones());
    }
}
