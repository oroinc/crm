<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use PHPUnit\Framework\TestCase;

class B2bCustomerEmailTest extends TestCase
{
    private B2bCustomerEmail $email;

    #[\Override]
    protected function setUp(): void
    {
        $this->email = new B2bCustomerEmail();
    }

    public function testOwner(): void
    {
        $this->assertNull($this->email->getOwner());

        $customer = new B2bCustomer();
        $this->email->setOwner($customer);

        $this->assertEquals($customer, $this->email->getOwner());
        $this->assertContains($this->email, $customer->getEmails());
    }
}
