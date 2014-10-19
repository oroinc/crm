<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\MagentoBundle\Entity\Address;

class AddressTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Address';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $owner      = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer');
        $originId = 123;

        return [
            'owner'     => ['owner', $owner, $owner],
            'origin_id' => ['originId', $originId, $originId],
        ];
    }

    public function testGetPhoneNumber()
    {
        $address = new Address();

        $this->assertNull($address->getPhoneNumber());

        $address->setContactPhone(new ContactPhone('123-123'));
        $this->assertEquals('123-123', $address->getPhoneNumber());

        $address->setPhone('456-456');
        $this->assertSame('456-456', $address->getPhoneNumber());
    }

    public function testGetPhoneNumbers()
    {
        $address = new Address();

        $this->assertSame([], $address->getPhoneNumbers());

        $address->setContactPhone(new ContactPhone('123-123'));
        $this->assertSame(['123-123'], $address->getPhoneNumbers());

        $address->setPhone('456-456');
        $this->assertEquals(['456-456', '123-123'], $address->getPhoneNumbers());

        $address->getContactPhone()->setPhone('456-456');
        $this->assertSame(['456-456'], $address->getPhoneNumbers());
    }
}
