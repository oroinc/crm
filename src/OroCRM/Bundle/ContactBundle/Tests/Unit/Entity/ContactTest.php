<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class ContactTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGroupLabels()
    {
        $entity = new Contact();
        $this->assertEquals(array(), $entity->getGroupLabels());

        $groupOne = new Group('Group One');
        $entity->addGroup($groupOne);
        $this->assertEquals(array('Group One'), $entity->getGroupLabels());

        $groupTwo = new Group('Group Two');
        $entity->addGroup($groupTwo);
        $this->assertEquals(array('Group One', 'Group Two'), $entity->getGroupLabels());

        $entity->removeGroup($groupOne);
        $this->assertEquals(array('Group Two'), $entity->getGroupLabels());
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
        $emailOne = new ContactEmail('email-one@example.com');
        $emailTwo = new ContactEmail('email-two@example.com');
        $emailThree = new ContactEmail('email-three@example.com');
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

        $contact->setPrimaryEmail($email);
        $this->assertSame($email, $contact->getPrimaryEmail());

        $email2 = new ContactEmail('new@example.com');
        $contact->addEmail($email2);
        $contact->setPrimaryEmail($email2);

        $this->assertSame($email2, $contact->getPrimaryEmail());
        $this->assertFalse($email->isPrimary());
    }

    public function testAddEmailShouldNotAllowMultiplePrimaries()
    {
        $contact = new Contact();

        $email = new ContactEmail('email@example.com');
        $email->setPrimary(true);
        $email2 = new ContactEmail('email2@example.com');
        $email2->setPrimary(true);

        $contact->addEmail($email);
        $contact->addEmail($email2);

        $primaryElements = $contact->getEmails()->filter(function ($element) {
            return $element->isPrimary();
        });

        $this->assertSame($email, $contact->getPrimaryEmail());
        $this->assertCount(1, $primaryElements);
    }

    public function testResetEmailsShouldNotAllowMultiplePrimaries()
    {
        $contact = new Contact();

        $email = new ContactEmail('email@example.com');
        $email->setPrimary(true);
        $email2 = new ContactEmail('email2@example.com');
        $email2->setPrimary(true);

        $contact->resetEmails([$email, $email2]);

        $primaryElements = $contact->getEmails()->filter(function ($element) {
            return $element->isPrimary();
        });

        $this->assertSame($email, $contact->getPrimaryEmail());
        $this->assertCount(1, $primaryElements);
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

        $contact->setPrimaryPhone($phone);
        $this->assertSame($phone, $contact->getPrimaryPhone());

        $phone2 = new ContactPhone('22001122334455');
        $contact->addPhone($phone2);
        $contact->setPrimaryPhone($phone2);

        $this->assertSame($phone2, $contact->getPrimaryPhone());
        $this->assertFalse($phone->isPrimary());
    }

    public function testAddPhoneShouldNotAllowMultiplePrimaries()
    {
        $contact = new Contact();

        $phone = new ContactPhone('06001122334455');
        $phone->setPrimary(true);
        $phone2 = new ContactPhone('22001122334455');
        $phone->setPrimary(true);

        $contact->addPhone($phone);
        $contact->addPhone($phone2);

        $primaryElements = $contact->getPhones()->filter(function ($element) {
            return $element->isPrimary();
        });

        $this->assertSame($phone, $contact->getPrimaryPhone());
        $this->assertCount(1, $primaryElements);
    }

    public function testResetPhonesShouldNotAllowMultiplePrimaries()
    {
        $contact = new Contact();

        $phone = new ContactPhone('06001122334455');
        $phone->setPrimary(true);
        $phone2 = new ContactPhone('22001122334455');
        $phone->setPrimary(true);

        $contact->resetPhones([$phone, $phone2]);

        $primaryElements = $contact->getPhones()->filter(function ($element) {
            return $element->isPrimary();
        });

        $this->assertSame($phone, $contact->getPrimaryPhone());
        $this->assertCount(1, $primaryElements);
    }

    public function testAddresses()
    {
        $addressOne = new ContactAddress();
        $addressOne->setCountry(new Country('US'));
        $addressTwo = new ContactAddress();
        $addressTwo->setCountry(new Country('UK'));
        $addressThree = new ContactAddress();
        $addressThree->setCountry(new Country('RU'));
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

        $newPrimary = new ContactAddress();
        $contact->addAddress($newPrimary);

        $contact->setPrimaryAddress($newPrimary);
        $this->assertSame($newPrimary, $contact->getPrimaryAddress());

        $this->assertFalse($address->isPrimary());
    }

    public function testAddAddressShouldNotAllowMultiplePrimaries()
    {
        $contact = new Contact();

        $address = new ContactAddress();
        $address->setPrimary(true);
        $address2 = new ContactAddress();
        $address->setPrimary(true);

        $contact->addAddress($address);
        $contact->addAddress($address2);

        $primaryElements = $contact->getAddresses()->filter(function ($element) {
            return $element->isPrimary();
        });

        $this->assertSame($address, $contact->getPrimaryAddress());
        $this->assertCount(1, $primaryElements);
    }

    public function testResetAddressesShouldNotAllowMultiplePrimaries()
    {
        $contact = new Contact();

        $address = new ContactAddress();
        $address->setPrimary(true);
        $address2 = new ContactAddress();
        $address->setPrimary(true);

        $contact->resetAddresses([$address, $address2]);

        $primaryElements = $contact->getAddresses()->filter(function ($element) {
            return $element->isPrimary();
        });

        $this->assertSame($address, $contact->getPrimaryAddress());
        $this->assertCount(1, $primaryElements);
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
        $contact->setNamePrefix('Mr.');
        $contact->setFirstName('First');
        $contact->setMiddleName('Middle');
        $contact->setLastName('Last');
        $contact->setNameSuffix('Sn.');

        $this->getFirstNameTest($contact);
        $this->toStringTest($contact);
    }

    public function testToStringsPartial()
    {
        $contact = new Contact();
        $contact->setFirstName('First');
        $contact->setLastName('Last');

        $this->assertEquals('First Last', $contact->__toString());
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
        $this->assertEquals('Mr. First Middle Last Sn.', $contact->__toString());
    }

    /**
     * @dataProvider flatPropertiesDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Contact();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function testSetAddressType()
    {
        $contact = new Contact();

        $shippingType = new AddressType('shipping');
        $addressOne = new ContactAddress();
        $addressOne->addType($shippingType);
        $contact->addAddress($addressOne);

        $addressTwo = new ContactAddress();
        $contact->addAddress($addressTwo);

        $contact->setAddressType($addressTwo, $shippingType);
        $this->assertFalse($addressOne->hasTypeWithName('shipping'));
        $this->assertTrue($addressTwo->hasTypeWithName('shipping'));
    }

    public function flatPropertiesDataProvider()
    {
        $now = new \DateTime('now');
        $user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User');
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Source');
        $source = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Source');
        $method = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Method');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return array(
            'namePrefix' => array('namePrefix', 'test', 'test'),
            'firstName' => array('firstName', 'test', 'test'),
            'middleName' => array('middleName', 'test', 'test'),
            'lastName' => array('lastName', 'test', 'test'),
            'nameSuffix' => array('nameSuffix', 'test', 'test'),
            'gender' => array('gender', 'male', 'male'),
            'assignedTo' => array('assignedTo', $user, $user),
            'birthday' => array('birthday', $now, $now),
            'description' => array('description', 'test', 'test'),
            'source' => array('source', $source, $source),
            'method' => array('method', $method, $method),
            'owner' => array('owner', $user, $user),
            'reportsTo' => array('reportsTo', $contact, $contact),
            'jobTitle' => array('jobTitle', 'test', 'test'),
            'fax' => array('fax', 'test', 'test'),
            'skype' => array('skype', 'test', 'test'),
            'facebook' => array('facebook', 'test', 'test'),
            'linkedIn' => array('linkedIn', 'test', 'test'),
            'googlePlus' => array('googlePlus', 'test', 'test'),
            'twitter' => array('twitter', 'test', 'test'),
            'createdAt' => array('createdAt', $now, $now),
            'updatedAt' => array('updatedAt', $now, $now),
            'createdBy' => array('createdBy', $user, $user),
            'updatedBy' => array('updatedBy', $user, $user),
            'organization' => array('organization', $organization, $organization),
        );
    }

    public function testHasAccounts()
    {
        $contact = new Contact();
        $this->assertFalse($contact->hasAccounts());

        $contact->addAccount(new Account());
        $this->assertTrue($contact->hasAccounts());
    }

    public function testHasEmail()
    {
        $email = new ContactEmail();

        $contact = new Contact();
        $this->assertFalse($contact->hasEmail($email));

        $contact->addEmail($email);
        $this->assertTrue($contact->hasEmail($email));
    }

    public function testHasPhone()
    {
        $phone = new ContactPhone();

        $contact = new Contact();
        $this->assertFalse($contact->hasPhone($phone));

        $contact->addPhone($phone);
        $this->assertTrue($contact->hasPhone($phone));
    }

    public function testGetEmail()
    {
        $contact = new Contact();
        $this->assertNull($contact->getEmail());

        $email = new ContactEmail('email@example.com');
        $contact->addEmail($email);
        $this->assertNull($contact->getEmail());

        $contact->setPrimaryEmail($email);
        $this->assertEquals('email@example.com', $contact->getEmail());
    }
}
