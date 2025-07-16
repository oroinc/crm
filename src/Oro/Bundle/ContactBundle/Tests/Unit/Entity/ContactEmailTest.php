<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use PHPUnit\Framework\TestCase;

class ContactEmailTest extends TestCase
{
    private ContactEmail $email;

    #[\Override]
    protected function setUp(): void
    {
        $this->email = new ContactEmail();
    }

    public function testOwner(): void
    {
        $this->assertNull($this->email->getOwner());

        $contact = new Contact();
        $this->email->setOwner($contact);

        $this->assertEquals($contact, $this->email->getOwner());
    }
}
