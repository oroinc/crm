<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use OroCRM\Bundle\SalesBundle\Provider\B2bCustomerPhoneProvider;

class B2bCustomerPhoneProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $rootProvider;

    /** @var B2bCustomerPhoneProvider */
    protected $provider;

    protected function setUp()
    {
        $this->rootProvider = $this->getMock('Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface');
        $this->provider = new B2bCustomerPhoneProvider();
        $this->provider->setRootProvider($this->rootProvider);
    }

    public function testGetPhoneNumber()
    {
        $entity = new B2bCustomer();
        $this->assertNull(
            $this->provider->getPhoneNumber($entity)
        );

        $phone1 = new B2bCustomerPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new B2bCustomerPhone('456-456');
        $phone2->setPrimary(true);
        $entity->addPhone($phone2);
        $this->assertEquals(
            '456-456',
            $this->provider->getPhoneNumber($entity)
        );
    }

    public function testGetPhoneNumbers()
    {
        $entity = new B2bCustomer();

        $this->assertSame(
            [],
            $this->provider->getPhoneNumbers($entity)
        );
        $phone1 = new B2bCustomerPhone('123-123');
        $entity->addPhone($phone1);
        $phone2 = new B2bCustomerPhone('456-456');
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
