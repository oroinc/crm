<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;

class ContactAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testOwner()
    {
        $contact = $this->createMock(Contact::class);
        $address = new ContactAddress();
        $address->setOwner($contact);
        $this->assertSame($contact, $address->getOwner());
    }
}
