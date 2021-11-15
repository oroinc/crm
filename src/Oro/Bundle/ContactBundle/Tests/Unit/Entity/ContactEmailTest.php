<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;

class ContactEmailTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactEmail */
    private $email;

    protected function setUp(): void
    {
        $this->email = new ContactEmail();
    }

    public function testOwner()
    {
        $this->assertNull($this->email->getOwner());

        $contact = new Contact();
        $this->email->setOwner($contact);

        $this->assertEquals($contact, $this->email->getOwner());
    }
}
