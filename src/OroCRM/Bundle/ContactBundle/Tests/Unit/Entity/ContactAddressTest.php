<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

class ContactAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testOwner()
    {
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();
        $address = new ContactAddress();
        $address->setOwner($contact);
        $this->assertSame($contact, $address->getOwner());
    }
}
