<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;

class ContactTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGroupLabels()
    {
        $entity = new Contact();
        $this->assertEquals(array(), $entity->getGroupLabels());

        $entity->addGroup(new Group('Group One'));
        $this->assertEquals(array('Group One'), $entity->getGroupLabels());

        $entity->addGroup(new Group('Group Two'));
        $this->assertEquals(array('Group One', 'Group Two'), $entity->getGroupLabels());
    }

    public function testGetGroupLabelsAsString()
    {
        $entity = new Contact();
        $this->assertEquals('', $entity->getGroupLabelsAsString());

        $entity->addGroup(new Group('Group One'));
        $this->assertEquals('Group One', $entity->getGroupLabelsAsString());

        $entity->addGroup(new Group('Group Two'));
        $this->assertEquals('Group One, Group Two', $entity->getGroupLabelsAsString());
    }

    public function testAddAccount()
    {
        $account = new Account();
        $account->setId(1);

        $contact = new Contact();
        $contact->setId(2);

        $this->assertEmpty($contact->getAccounts()->toArray());

        $contact->addAccount($account);
        $actualAccounts = $contact->getAccounts()->toArray();
        $this->assertCount(1, $actualAccounts);
        $this->assertEquals($account, current($actualAccounts));
    }

    public function testRemoveAccount()
    {
        $account = new Account();
        $account->setId(1);

        $contact = new Contact();
        $contact->setId(2);

        $contact->addAccount($account);
        $this->assertCount(1, $contact->getAccounts()->toArray());

        $contact->removeAccount($account);
        $this->assertEmpty($contact->getAccounts()->toArray());
    }

    public function testEmails()
    {
        $emailOne = new ContactEmail('emailone@example.com');
        $emailTwo = new ContactEmail('emailtwo@example.com');
        $emailThree = new ContactEmail('emailthree@example.com');
        $emails = array($emailOne, $emailTwo);

        $contact = new Contact();
        $this->assertSame($contact, $contact->resetEmails($emails));
        $actual = $contact->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($emails, $actual->toArray());

        $this->assertSame($contact, $contact->addEmail($emailTwo));
        $actual = $contact->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($emails, $actual->toArray());

        $this->assertSame($contact, $contact->addEmail($emailThree));
        $actual = $contact->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($emailOne, $emailTwo, $emailThree), $actual->toArray());

        $this->assertSame($contact, $contact->removeEmail($emailOne));
        $actual = $contact->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $emailTwo, 2 => $emailThree), $actual->toArray());

        $this->assertSame($contact, $contact->removeEmail($emailOne));
        $actual = $contact->getEmails();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $emailTwo, 2 => $emailThree), $actual->toArray());
    }

    public function testGetPrimaryEmail()
    {
        $contact = new Contact();
        $this->assertNull($contact->getPrimaryEmail());

        $email = new ContactEmail('email@example.com');
        $contact->addEmail($email);
        $this->assertNull($contact->getPrimaryEmail());

        $email->setPrimary(true);
        $this->assertSame($email, $contact->getPrimaryEmail());
    }

    public function testPhones()
    {
        $phoneOne = new ContactPhone('06001122334455');
        $phoneTwo = new ContactPhone('07001122334455');
        $phoneThree = new ContactPhone('08001122334455');
        $phones = array($phoneOne, $phoneTwo);

        $contact = new Contact();
        $this->assertSame($contact, $contact->resetPhones($phones));
        $actual = $contact->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($contact, $contact->addPhone($phoneTwo));
        $actual = $contact->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($contact, $contact->addPhone($phoneThree));
        $actual = $contact->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($phoneOne, $phoneTwo, $phoneThree), $actual->toArray());

        $this->assertSame($contact, $contact->removePhone($phoneOne));
        $actual = $contact->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $phoneTwo, 2 => $phoneThree), $actual->toArray());

        $this->assertSame($contact, $contact->removePhone($phoneOne));
        $actual = $contact->getPhones();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $phoneTwo, 2 => $phoneThree), $actual->toArray());
    }

    public function testGetPrimaryPhone()
    {
        $contact = new Contact();
        $this->assertNull($contact->getPrimaryPhone());

        $phone = new ContactPhone('06001122334455');
        $contact->addPhone($phone);
        $this->assertNull($contact->getPrimaryPhone());

        $phone->setPrimary(true);
        $this->assertSame($phone, $contact->getPrimaryPhone());
    }

    public function testAddresses()
    {
        $addressOne = new ContactAddress();
        $addressOne->setCountry('US');
        $addressTwo = new ContactAddress();
        $addressTwo->setCountry('UK');
        $addressThree = new ContactAddress();
        $addressThree->setCountry('RU');
        $addresses = array($addressOne, $addressTwo);

        $contact = new Contact();
        $this->assertSame($contact, $contact->resetAddresses($addresses));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($contact, $contact->addAddress($addressTwo));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($contact, $contact->addAddress($addressThree));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($addressOne, $addressTwo, $addressThree), $actual->toArray());

        $this->assertSame($contact, $contact->removeAddress($addressOne));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());

        $this->assertSame($contact, $contact->removeAddress($addressOne));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());
    }

    public function testGetPrimaryAddress()
    {
        $contact = new Contact();
        $this->assertNull($contact->getPrimaryAddress());

        $address = new ContactAddress();
        $contact->addAddress($address);
        $this->assertNull($contact->getPrimaryAddress());

        $address->setPrimary(true);
        $this->assertSame($address, $contact->getPrimaryAddress());
    }

    public function testGetAddressByTypeName()
    {
        $contact = new Contact();
        $this->assertNull($contact->getAddressByTypeName('billing'));

        $address = new ContactAddress();
        $address->addType(new AddressType('billing'));
        $contact->addAddress($address);

        $this->assertSame($address, $contact->getAddressByTypeName('billing'));
    }

    public function testGetAddressByType()
    {
        $address = new ContactAddress();
        $addressType = new AddressType('billing');
        $address->addType($addressType);

        $contact = new Contact();
        $this->assertNull($contact->getAddressByType($addressType));

        $contact->addAddress($address);
        $this->assertSame($address, $contact->getAddressByType($addressType));
    }

    public function testToStringNoAttributes()
    {
        $contact = new Contact();
        $this->assertEquals('', $contact->__toString());
    }

    public function testNames()
    {
        $contact = new Contact();
        $contact->setFirstName('First');
        $contact->setLastName('Last');

        $this->getFirstNameTest($contact);
        $this->toStringTest($contact);
        $this->getFullNameTest($contact);
    }

    /**
     * @param \OroCRM\Bundle\ContactBundle\Entity\Contact $contact
     */
    protected function getFirstNameTest($contact)
    {
        $this->assertEquals('First', $contact->getFirstName());
    }

    /**
     * @param \OroCRM\Bundle\ContactBundle\Entity\Contact $contact
     */
    protected function toStringTest($contact)
    {
        $this->assertEquals('First Last', $contact->__toString());
    }

    /**
     * @param \OroCRM\Bundle\ContactBundle\Entity\Contact $contact
     */
    protected function getFullNameTest($contact)
    {
        $this->assertEquals($contact->getFullname(), sprintf('%s %s', 'First', 'Last'));

        $contact->setNameFormat('%last%, %first%');

        $this->assertEquals($contact->getFullname(), sprintf('%s, %s', 'Last', 'First'));
    }
}
