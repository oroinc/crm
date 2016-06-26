<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadPhone;

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
        $now          = new \DateTime('now');
        $user         = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $address      = $this->getMock('Oro\Bundle\AddressBundle\Entity\Address');
        $customer     = $this->getMock('OroCRM\Bundle\SalesBundle\Entity\B2bCustomer');
        $channel      = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return [
            'name'              => ['name', 'test', 'test'],
            'namePrefix'        => ['namePrefix', 'test', 'test'],
            'firstName'         => ['firstName', 'test', 'test'],
            'middleName'        => ['middleName', 'test', 'test'],
            'lastName'          => ['lastName', 'test', 'test'],
            'nameSuffix'        => ['nameSuffix', 'test', 'test'],
            'numberOfEmployees' => ['numberOfEmployees', 10, 10],
            'website'           => ['website', 'test', 'test'],
            'companyName'       => ['companyName', 'test', 'test'],
            'email'             => ['email', 'test', 'test'],
            'jobTitle'          => ['jobTitle', 'test', 'test'],
            'industry'          => ['nameSuffix', 'test', 'test'],
            'address'           => ['owner', $address, $address],
            'owner'             => ['owner', $user, $user],
            'createdAt'         => ['createdAt', $now, $now],
            'updatedAt'         => ['updatedAt', $now, $now],
            'notes'             => ['notes', 'test', 'test'],
            'customer'          => ['customer', $customer, $customer],
            'dataChannel'       => ['dataChannel', $channel, $channel],
            'organization'      => array('organization', $organization, $organization)
        ];
    }

    public function testPhones()
    {
        $phoneOne = new LeadPhone('06001122334455');
        $phoneTwo = new LeadPhone('07001122334455');
        $phoneThree = new LeadPhone('08001122334455');
        $phones = array($phoneOne, $phoneTwo);

        $lead = new Lead();
        $this->assertSame($lead, $lead->resetPhones($phones));
        $actual = $lead->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($lead, $lead->addPhone($phoneTwo));
        $actual = $lead->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($lead, $lead->addPhone($phoneThree));
        $actual = $lead->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($phoneOne, $phoneTwo, $phoneThree), $actual->toArray());

        $this->assertSame($lead, $lead->removePhone($phoneOne));
        $actual = $lead->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $phoneTwo, 2 => $phoneThree), $actual->toArray());

        $this->assertSame($lead, $lead->removePhone($phoneOne));
        $actual = $lead->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $phoneTwo, 2 => $phoneThree), $actual->toArray());
    }

    public function testGetPrimaryPhone()
    {
        $Lead = new Lead();
        $this->assertNull($Lead->getPrimaryPhone());

        $phone = new LeadPhone('06001122334455');
        $Lead->addPhone($phone);
        $this->assertNull($Lead->getPrimaryPhone());

        $Lead->setPrimaryPhone($phone);
        $this->assertSame($phone, $Lead->getPrimaryPhone());

        $phone2 = new LeadPhone('22001122334455');
        $Lead->addPhone($phone2);
        $Lead->setPrimaryPhone($phone2);

        $this->assertSame($phone2, $Lead->getPrimaryPhone());
        $this->assertFalse($phone->isPrimary());
    }
}
