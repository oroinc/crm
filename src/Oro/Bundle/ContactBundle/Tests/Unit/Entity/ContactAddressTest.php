<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use PHPUnit\Framework\TestCase;

class ContactAddressTest extends TestCase
{
    public function testOwner(): void
    {
        $contact = new Contact();
        $address = new ContactAddress();

        $address->setOwner($contact);
        self::assertSame($contact, $address->getOwner());
        self::assertCount(1, $contact->getAddresses());
        self::assertSame($address, $contact->getAddresses()->first());

        $address->setOwner($contact);
        self::assertSame($contact, $address->getOwner());
        self::assertCount(1, $contact->getAddresses());
        self::assertSame($address, $contact->getAddresses()->first());

        $address->setOwner(null);
        self::assertNull($address->getOwner());
        self::assertCount(0, $contact->getAddresses());
    }
}
