<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Provider;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Provider\ContactPhoneProvider;

class ContactPhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactPhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new ContactPhoneProvider();
    }

    public function testGetPhoneNumber()
    {
        $entity = new Contact();

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $phone1 = new ContactPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new ContactPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);

        $this->assertEquals(
            '456-456',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity = new Contact();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );

        $phone1 = new ContactPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new ContactPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);

        $this->assertSame(
            [
                ['123-123', $entity],
                ['456-456', $entity]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
