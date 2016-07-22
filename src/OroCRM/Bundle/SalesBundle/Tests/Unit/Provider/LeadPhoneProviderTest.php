<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadPhone;
use OroCRM\Bundle\SalesBundle\Provider\LeadPhoneProvider;

class LeadPhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var LeadPhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new LeadPhoneProvider();
    }

    public function testGetPhoneNumber()
    {
        $entity = new Lead();

        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $phone1 = new LeadPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new LeadPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);

        $this->assertEquals(
            '456-456',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity = new Lead();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );

        $phone1 = new LeadPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new LeadPhone('456-456');
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
