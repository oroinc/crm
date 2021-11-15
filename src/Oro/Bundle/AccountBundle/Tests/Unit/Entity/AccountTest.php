<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Entity;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

class AccountTest extends \PHPUnit\Framework\TestCase
{
    public function testGettersSetters()
    {
        $entity = new Account();
        $entity->setName('Test');

        $this->assertEquals('Test', $entity->getName());
        $this->assertEquals('Test', (string)$entity);

        $organization = new Organization();
        $this->assertNull($entity->getOrganization());
        $entity->setOrganization($organization);
        $this->assertSame($organization, $entity->getOrganization());
    }

    public function testBeforeSave()
    {
        $entity = new Account();
        $entity->beforeSave();
        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
    }

    public function testDoPreUpdate()
    {
        $entity = new Account();
        $entity->doPreUpdate();
        $this->assertInstanceOf(\DateTime::class, $entity->getUpdatedAt());
    }

    public function testAddContact()
    {
        $account = new Account();
        $account->setId(1);

        $contact = new Contact();
        $contact->setId(2);

        $this->assertEmpty($account->getContacts()->toArray());

        $account->addContact($contact);
        $actualContacts = $account->getContacts()->toArray();
        $this->assertCount(1, $actualContacts);
        $this->assertEquals($contact, current($actualContacts));
    }

    public function testRemoveContact()
    {
        $account = new Account();
        $account->setId(1);

        $contact = new Contact();
        $contact->setId(2);

        $account->addContact($contact);
        $this->assertCount(1, $account->getContacts()->toArray());

        $account->removeContact($contact);
        $this->assertEmpty($account->getContacts()->toArray());
    }

    public function testOwners()
    {
        $entity = new Account();
        $user = new User();

        $this->assertEmpty($entity->getOwner());

        $entity->setOwner($user);

        $this->assertEquals($user, $entity->getOwner());
    }

    public function testGetEmail()
    {
        $account = new Account();
        $contact = $this->createMock(Contact::class);

        $this->assertNull($account->getEmail());

        $account->setDefaultContact($contact);
        $contact->expects($this->once())
            ->method('getEmail')
            ->willReturn('email@example.com');
        $this->assertEquals('email@example.com', $account->getEmail());
    }

    public function testSetDefaultContact()
    {
        $account = new Account();
        $this->assertNull($account->getDefaultContact());

        $contact = new Contact();
        $account->setDefaultContact($contact);
        $this->assertSame($contact, $account->getDefaultContact());
        $this->assertCount(1, $contact->getDefaultInAccounts());
        $this->assertSame($account, $contact->getDefaultInAccounts()->first());

        $contact2 = new Contact();
        $account->setDefaultContact($contact2);
        $this->assertCount(0, $contact->getDefaultInAccounts());
        $this->assertCount(1, $contact2->getDefaultInAccounts());
        $this->assertSame($contact2, $account->getDefaultContact());
        $this->assertSame($account, $contact2->getDefaultInAccounts()->first());
    }
}
