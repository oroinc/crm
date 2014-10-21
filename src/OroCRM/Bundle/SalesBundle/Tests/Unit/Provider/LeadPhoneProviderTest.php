<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
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

        $entity->setPhoneNumber('123-123');
        $this->assertEquals(
            '123-123',
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

        $entity->setPhoneNumber('123-123');
        $this->assertEquals(
            [
                ['123-123', $entity]
            ],
            $this->provider->getPhoneNumbers($entity)
        );
    }
}
