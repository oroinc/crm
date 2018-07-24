<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\ContactAddress;

class ContactAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testOwner()
    {
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();
        $address = new ContactAddress();
        $address->setOwner($contact);
        $this->assertSame($contact, $address->getOwner());
    }
}
