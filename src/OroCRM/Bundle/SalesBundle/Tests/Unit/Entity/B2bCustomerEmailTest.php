<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail;

class B2bCustomerEmailTest extends \PHPUnit_Framework_TestCase
{
    protected $email;

    protected function setUp()
    {
        $this->email = new B2bCustomerEmail();
    }

    public function testOwner()
    {
        $this->assertNull($this->email->getOwner());

        $contact = new B2bCustomer();
        $this->email->setOwner($contact);

        $this->assertEquals($contact, $this->email->getOwner());
    }
}
