<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class ContactTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSave()
    {
        $entity = new Contact();
        $entity->beforeSave();
        $this->assertInstanceOf('\DateTime', $entity->getCreatedAt());
    }

    public function testDoPreUpdate()
    {
        $entity = new Contact();
        $entity->doPreUpdate();
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
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
}
