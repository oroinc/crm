<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Entity\Method;
use Oro\Bundle\ContactBundle\Entity\Source;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ContactTest extends \PHPUnit\Framework\TestCase
{
    public function testGetGroupLabels()
    {
        $entity = new Contact();
        $this->assertEquals([], $entity->getGroupLabels());

        $groupOne = new Group('Group One');
        $entity->addGroup($groupOne);
        $this->assertEquals(['Group One'], $entity->getGroupLabels());

        $groupTwo = new Group('Group Two');
        $entity->addGroup($groupTwo);
        $this->assertEquals(['Group One', 'Group Two'], $entity->getGroupLabels());

        $entity->removeGroup($groupOne);
        $this->assertEquals(['Group Two'], $entity->getGroupLabels());
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
        $emails = [$emailOne, $emailTwo];

        $contact = new Contact();
        $this->assertSame($contact, $contact->resetEmails($emails));
        $actual = $contact->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($emails, $actual->toArray());

        $this->assertSame($contact, $contact->addEmail($emailTwo));
        $actual = $contact->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($emails, $actual->toArray());

        $this->assertSame($contact, $contact->addEmail($emailThree));
        $actual = $contact->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([$emailOne, $emailTwo, $emailThree], $actual->toArray());

        $this->assertSame($contact, $contact->removeEmail($emailOne));
        $actual = $contact->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $emailTwo, 2 => $emailThree], $actual->toArray());

        $this->assertSame($contact, $contact->removeEmail($emailOne));
        $actual = $contact->getEmails();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $emailTwo, 2 => $emailThree], $actual->toArray());
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
        $phones = [$phoneOne, $phoneTwo];

        $contact = new Contact();
        $this->assertSame($contact, $contact->resetPhones($phones));
        $actual = $contact->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($contact, $contact->addPhone($phoneTwo));
        $actual = $contact->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($phones, $actual->toArray());

        $this->assertSame($contact, $contact->addPhone($phoneThree));
        $actual = $contact->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([$phoneOne, $phoneTwo, $phoneThree], $actual->toArray());

        $this->assertSame($contact, $contact->removePhone($phoneOne));
        $actual = $contact->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $phoneTwo, 2 => $phoneThree], $actual->toArray());

        $this->assertSame($contact, $contact->removePhone($phoneOne));
        $actual = $contact->getPhones();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $phoneTwo, 2 => $phoneThree], $actual->toArray());
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
        $addresses = [$addressOne, $addressTwo];

        $contact = new Contact();
        $this->assertSame($contact, $contact->resetAddresses($addresses));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($contact, $contact->addAddress($addressTwo));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($contact, $contact->addAddress($addressThree));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([$addressOne, $addressTwo, $addressThree], $actual->toArray());

        $this->assertSame($contact, $contact->removeAddress($addressOne));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $addressTwo, 2 => $addressThree], $actual->toArray());

        $this->assertSame($contact, $contact->removeAddress($addressOne));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf(ArrayCollection::class, $actual);
        $this->assertEquals([1 => $addressTwo, 2 => $addressThree], $actual->toArray());
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

        $this->assertEquals('First', $contact->getFirstName());
        $this->assertEquals('Mr. First Middle Last Sn.', (string)$contact);
    }

    public function testToStringsPartial()
    {
        $contact = new Contact();
        $contact->setFirstName('First');
        $contact->setLastName('Last');

        $this->assertEquals('First Last', $contact->__toString());
    }

    /**
     * @dataProvider flatPropertiesDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Contact();

        call_user_func([$obj, 'set' . ucfirst($property)], $value);
        $this->assertEquals($expected, call_user_func_array([$obj, 'get' . ucfirst($property)], []));
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

    public function flatPropertiesDataProvider(): array
    {
        $now = new \DateTime('now');
        $user = $this->createMock(User::class);
        $contact = $this->createMock(Source::class);
        $source = $this->createMock(Source::class);
        $method = $this->createMock(Method::class);
        $organization = $this->createMock(Organization::class);

        return [
            'namePrefix' => ['namePrefix', 'test', 'test'],
            'firstName' => ['firstName', 'test', 'test'],
            'middleName' => ['middleName', 'test', 'test'],
            'lastName' => ['lastName', 'test', 'test'],
            'nameSuffix' => ['nameSuffix', 'test', 'test'],
            'gender' => ['gender', 'male', 'male'],
            'assignedTo' => ['assignedTo', $user, $user],
            'birthday' => ['birthday', $now, $now],
            'description' => ['description', 'test', 'test'],
            'source' => ['source', $source, $source],
            'method' => ['method', $method, $method],
            'owner' => ['owner', $user, $user],
            'reportsTo' => ['reportsTo', $contact, $contact],
            'jobTitle' => ['jobTitle', 'test', 'test'],
            'fax' => ['fax', 'test', 'test'],
            'skype' => ['skype', 'test', 'test'],
            'facebook' => ['facebook', 'test', 'test'],
            'linkedIn' => ['linkedIn', 'test', 'test'],
            'googlePlus' => ['googlePlus', 'test', 'test'],
            'twitter' => ['twitter', 'test', 'test'],
            'createdAt' => ['createdAt', $now, $now],
            'updatedAt' => ['updatedAt', $now, $now],
            'createdBy' => ['createdBy', $user, $user],
            'updatedBy' => ['updatedBy', $user, $user],
            'organization' => ['organization', $organization, $organization],
        ];
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
