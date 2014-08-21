<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Lead();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $now = new \DateTime('now');

        $user        = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $address     = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $customer = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\B2BCustomer')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'namePrefix'        => ['namePrefix', 'test', 'test'],
            'firstName'         => ['firstName', 'test', 'test'],
            'middleName'        => ['middleName', 'test', 'test'],
            'lastName'          => ['lastName', 'test', 'test'],
            'nameSuffix'        => ['nameSuffix', 'test', 'test'],
            'numberOfEmployees' => ['numberOfEmployees', 10, 10],
            'website'           => ['website', 'test', 'test'],
            'companyName'       => ['companyName', 'test', 'test'],
            'email'             => ['email', 'test', 'test'],
            'phoneNumber'       => ['phoneNumber', 'test', 'test'],
            'jobTitle'          => ['jobTitle', 'test', 'test'],
            'industry'          => ['nameSuffix', 'test', 'test'],
            'address'           => ['owner', $address, $address],
            'owner'             => ['owner', $user, $user],
            'createdAt'         => ['createdAt', $now, $now],
            'updatedAt'         => ['updatedAt', $now, $now],
            'notes'             => ['notes', 'test', 'test'],
            'customer'          => ['customer', $customer, $customer],
        ];
    }
}
