<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;

class B2bCustomerEmailTest extends \PHPUnit\Framework\TestCase
{
    /** @var B2bCustomerEmail */
    private $email;

    protected function setUp(): void
    {
        $this->email = new B2bCustomerEmail();
    }

    public function testOwner()
    {
        $this->assertNull($this->email->getOwner());

        $customer = new B2bCustomer();
        $this->email->setOwner($customer);

        $this->assertEquals($customer, $this->email->getOwner());
        $this->assertContains($this->email, $customer->getEmails());
    }
}
