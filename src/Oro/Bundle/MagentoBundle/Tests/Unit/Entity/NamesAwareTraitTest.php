<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Tests\Unit\Fixtures\NamesAwareEntity;

class NamesAwareTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider updateProvider
     *
     * @param NamesAwareEntity $entity
     * @param string|null      $expectedFirstName
     * @param string|null      $expectedLastName
     */
    public function testUpdate(NamesAwareEntity $entity, $expectedFirstName, $expectedLastName)
    {
        $entity->doUpdate();

        static::assertEquals($expectedFirstName, $entity->getFirstName());
        static::assertEquals($expectedLastName, $entity->getLastName());
    }

    /**
     * @return array
     */
    public function updateProvider()
    {
        $testAddressFirstName = '$testAddressFirstName';
        $testAddressLastName  = '$testAddressLastName';

        $testCustomerFirstName = '$testCustomerFirstName';
        $testCustomerLastName  = '$testCustomerLastName';

        $address = $this->getMockBuilder(AbstractAddress::class)
            ->onlyMethods(['getFirstName', 'getLastName'])
            ->getMockForAbstractClass();
        $address->method('getFirstName')->willReturn($testAddressFirstName);
        $address->method('getLastName')->willReturn($testAddressLastName);

        $customer = $this->createMock(Customer::class);
        $customer->method('getFirstName')->willReturn($testCustomerFirstName);
        $customer->method('getLastName')->willReturn($testCustomerLastName);

        $entity1 = new NamesAwareEntity();
        $entity2 = new NamesAwareEntity($address);
        $entity3 = new NamesAwareEntity(null, $customer);
        $entity4 = new NamesAwareEntity($address, $customer);

        return [
            'empty data expected'                    => [$entity1, null, null],
            'data from address expected'             => [$entity2, $testAddressFirstName, $testAddressLastName],
            'data from customer expected'            => [$entity3, $testCustomerFirstName, $testCustomerLastName],
            'data from customer has higher priority' => [$entity4, $testCustomerFirstName, $testCustomerLastName]
        ];
    }
}
