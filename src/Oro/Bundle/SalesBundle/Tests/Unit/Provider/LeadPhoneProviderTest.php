<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Provider\LeadPhoneProvider;

class LeadPhoneProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeadPhoneProvider */
    private $provider;

    protected function setUp(): void
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
