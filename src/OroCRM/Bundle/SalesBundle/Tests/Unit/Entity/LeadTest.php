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

    public function getSetDataProvider()
    {
        $now = new \DateTime('now');
        $user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Address')
            ->disableOriginalConstructor()
            ->getMock();
        return array(
            'namePrefix' => array('namePrefix', 'test', 'test'),
            'firstName' => array('firstName', 'test', 'test'),
            'middleName' => array('middleName', 'test', 'test'),
            'lastName' => array('lastName', 'test', 'test'),
            'nameSuffix' => array('nameSuffix', 'test', 'test'),
            'numberOfEmployees' => array('numberOfEmployees', 10, 10),
            'website' => array('website', 'test', 'test'),
            'companyName' => array('companyName', 'test', 'test'),
            'email' => array('email', 'test', 'test'),
            'phoneNumber' => array('phoneNumber', 'test', 'test'),
            'jobTitle' => array('jobTitle', 'test', 'test'),
            'industry' => array('nameSuffix', 'test', 'test'),
            'address' => array('owner', $address, $address),
            'owner' => array('owner', $user, $user),
            'createdAt' => array('createdAt', $now, $now),
            'updatedAt' => array('updatedAt', $now, $now),
            'notes' => array('notes', 'test', 'test')
        );
    }
}
