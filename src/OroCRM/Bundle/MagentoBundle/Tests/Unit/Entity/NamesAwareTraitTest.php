<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\NamesAwareEntity;

class NamesAwareTraitTest extends \PHPUnit_Framework_TestCase
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

        $this->assertAttributeEquals($expectedFirstName, 'firstName', $entity);
        $this->assertAttributeEquals($expectedLastName, 'lastName', $entity);
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

        $address = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\AbstractAddress')
            ->setMethods(['getFirstName', 'getLastName'])->getMockForAbstractClass();
        $address->expects($this->any())->method('getFirstName')->will($this->returnValue($testAddressFirstName));
        $address->expects($this->any())->method('getLastName')->will($this->returnValue($testAddressLastName));

        $customer = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer');
        $customer->expects($this->any())->method('getFirstName')->will($this->returnValue($testCustomerFirstName));
        $customer->expects($this->any())->method('getLastName')->will($this->returnValue($testCustomerLastName));

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
