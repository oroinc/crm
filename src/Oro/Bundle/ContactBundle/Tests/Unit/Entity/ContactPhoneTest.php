<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use PHPUnit\Framework\TestCase;

class ContactPhoneTest extends TestCase
{
    private ContactPhone $phone;

    #[\Override]
    protected function setUp(): void
    {
        $this->phone = new ContactPhone();
    }

    public function testOwner(): void
    {
        $this->assertNull($this->phone->getOwner());

        $contact = new Contact();
        $this->phone->setOwner($contact);

        $this->assertEquals($contact, $this->phone->getOwner());
    }
}
