<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\Entity;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Oro\Bundle\UserBundle\Entity\User;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersSetters()
    {
        $entity = new Account();
        $entity->setName('Test');
        $this->assertEquals('Test', $entity->getName());
        $this->assertEquals('Test', (string)$entity);
    }

    public function testBeforeSave()
    {
        $entity = new Account();
        $entity->beforeSave();
        $this->assertInstanceOf('\DateTime', $entity->getCreatedAt());
    }

    public function testDoPreUpdate()
    {
        $entity = new Account();
        $entity->doPreUpdate();
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
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
}
