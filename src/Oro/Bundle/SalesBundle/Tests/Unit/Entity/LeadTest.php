<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\UserBundle\Entity\User;

class LeadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Lead();

        call_user_func([$obj, 'set' . ucfirst($property)], $value);
        $this->assertEquals($expected, call_user_func_array([$obj, 'get' . ucfirst($property)], []));
    }

    public function getSetDataProvider(): array
    {
        $now = new \DateTime('now');
        $user = $this->createMock(User::class);
        $customer = $this->createMock(Customer::class);
        $organization = $this->createMock(Organization::class);

        return [
            'name'                  => ['name', 'test', 'test'],
            'namePrefix'            => ['namePrefix', 'test', 'test'],
            'firstName'             => ['firstName', 'test', 'test'],
            'middleName'            => ['middleName', 'test', 'test'],
            'lastName'              => ['lastName', 'test', 'test'],
            'nameSuffix'            => ['nameSuffix', 'test', 'test'],
            'numberOfEmployees'     => ['numberOfEmployees', 10, 10],
            'website'               => ['website', 'test', 'test'],
            'companyName'           => ['companyName', 'test', 'test'],
            'jobTitle'              => ['jobTitle', 'test', 'test'],
            'industry'              => ['nameSuffix', 'test', 'test'],
            'owner'                 => ['owner', $user, $user],
            'createdAt'             => ['createdAt', $now, $now],
            'updatedAt'             => ['updatedAt', $now, $now],
            'notes'                 => ['notes', 'test', 'test'],
            'customerAssociation'   => ['customerAssociation', $customer, $customer],
            'organization'          => ['organization', $organization, $organization],
            'twitter'               => ['twitter', 'test', 'test'],
            'linkedIn'              => ['linkedIn', 'test', 'test'],
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
        $addresses = [$addressOne, $addressTwo];

        $lead = new Lead();
        $lead->addAddress($addressOne)->addAddress($addressTwo);
        $actual = $lead->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($lead, $lead->addAddress($addressTwo));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($lead, $lead->addAddress($addressThree));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([$addressOne, $addressTwo, $addressThree], $actual->toArray());

        $this->assertSame($lead, $lead->removeAddress($addressOne));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $addressTwo, 2 => $addressThree], $actual->toArray());

        $this->assertSame($lead, $lead->removeAddress($addressOne));
        $actual = $lead->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $addressTwo, 2 => $addressThree], $actual->toArray());
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

    public function testPhones()
    {
        $phoneOne = new LeadPhone('06001122334455');
        $phoneTwo = new LeadPhone('07001122334455');
        $phoneThree = new LeadPhone('08001122334455');
        $phones = [$phoneOne, $phoneTwo];

        $lead = new Lead();
        $this->assertSame($lead, $lead->resetPhones($phones));
        $actual = $lead->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($lead, $lead->addPhone($phoneTwo));
        $actual = $lead->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($lead, $lead->addPhone($phoneThree));
        $actual = $lead->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([$phoneOne, $phoneTwo, $phoneThree], $actual->toArray());

        $this->assertSame($lead, $lead->removePhone($phoneOne));
        $actual = $lead->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $phoneTwo, 2 => $phoneThree], $actual->toArray());

        $this->assertSame($lead, $lead->removePhone($phoneOne));
        $actual = $lead->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $phoneTwo, 2 => $phoneThree], $actual->toArray());
    }

    public function testGetPrimaryPhone()
    {
        $lead = new Lead();
        $this->assertNull($lead->getPrimaryPhone());

        $phone = new LeadPhone('06001122334455');
        $lead->addPhone($phone);
        $this->assertNull($lead->getPrimaryPhone());

        $lead->setPrimaryPhone($phone);
        $this->assertSame($phone, $lead->getPrimaryPhone());

        $phone2 = new LeadPhone('22001122334455');
        $lead->addPhone($phone2);
        $lead->setPrimaryPhone($phone2);

        $this->assertSame($phone2, $lead->getPrimaryPhone());
        $this->assertFalse($phone->isPrimary());
    }

    public function testEmails()
    {
        $emailOne = new LeadEmail('email-one@example.com');
        $emailTwo = new LeadEmail('email-two@example.com');
        $emailThree = new LeadEmail('email-three@example.com');
        $emails = [$emailOne, $emailTwo];

        $lead = new Lead();
        $this->assertSame($lead, $lead->resetEmails($emails));
        $actual = $lead->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($emails, $actual->toArray());

        $this->assertSame($lead, $lead->addEmail($emailTwo));
        $actual = $lead->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($emails, $actual->toArray());

        $this->assertSame($lead, $lead->addEmail($emailThree));
        $actual = $lead->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([$emailOne, $emailTwo, $emailThree], $actual->toArray());

        $this->assertSame($lead, $lead->removeEmail($emailOne));
        $actual = $lead->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $emailTwo, 2 => $emailThree], $actual->toArray());

        $this->assertSame($lead, $lead->removeEmail($emailOne));
        $actual = $lead->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $emailTwo, 2 => $emailThree], $actual->toArray());
    }

    public function testGetPrimaryEmail()
    {
        $lead = new Lead();
        $this->assertNull($lead->getPrimaryEmail());

        $email = new LeadEmail('email@example.com');
        $email->setPrimary(true);
        $lead->addEmail($email);
        $this->assertSame($email, $lead->getPrimaryEmail());
    }
}
