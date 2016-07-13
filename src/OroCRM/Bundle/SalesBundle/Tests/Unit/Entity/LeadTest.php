<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadAddress;

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
            'owner'             => ['owner', $user, $user],
            'createdAt'         => ['createdAt', $now, $now],
            'updatedAt'         => ['updatedAt', $now, $now],
            'notes'             => ['notes', 'test', 'test'],
            'customer'          => ['customer', $customer, $customer],
            'dataChannel'       => ['dataChannel', $channel, $channel],
            'organization'      => array('organization', $organization, $organization)
        ];
    }

    public function testAddresses()
    {
        $addressOne = new LeadAddress();
        $addressOne->setCountry(new Country('US'));
        $addressTwo = new LeadAddress();
        $addressTwo->setCountry(new Country('UK'));
        $addressThree = new LeadAddress();
        $addressThree->setCountry(new Country('RU'));
        $addresses = array($addressOne, $addressTwo);

        $lead = new Lead();
        $lead->addAddress($addressOne)->addAddress($addressTwo);
        $actual = $lead->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($lead, $lead->addAddress($addressTwo));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($lead, $lead->addAddress($addressThree));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($addressOne, $addressTwo, $addressThree), $actual->toArray());

        $this->assertSame($lead, $lead->removeAddress($addressOne));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());

        $this->assertSame($lead, $lead->removeAddress($addressOne));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());
    }

    public function testGetPrimaryAddress()
    {
        $lead = new Lead();
        $this->assertNull($lead->getPrimaryAddress());

        $address = new LeadAddress();
        $lead->addAddress($address);
        $this->assertNull($lead->getPrimaryAddress());

        $address->setPrimary(true);
        $this->assertSame($address, $lead->getPrimaryAddress());

        $newPrimary = new LeadAddress();
        $lead->addAddress($newPrimary);

        $lead->setPrimaryAddress($newPrimary);
        $this->assertSame($newPrimary, $lead->getPrimaryAddress());

        $this->assertFalse($address->isPrimary());
    }
}
