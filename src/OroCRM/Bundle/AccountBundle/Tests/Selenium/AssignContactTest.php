<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Accounts;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

/**
 * Class AssignContactTest
 *
 * @package OroCRM\Bundle\AccountBundle\Tests\Selenium\Accounts
 */
class AssignContactTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateContact()
    {
        $contactName = 'Contact_'.mt_rand();

        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->assertTitle('All - Contacts - Customers')
            ->add()
            ->assertTitle('Create Contact - Contacts - Customers')
            ->setFirstName($contactName . '_first')
            ->setLastName($contactName . '_last')
            ->setOwner('admin')
            ->setEmail($contactName . '@mail.com')
            ->save()
            ->assertMessage('Contact saved');

        return $contactName;
    }

    /**
     * @depends testCreateContact
     * @param $contactName
     * @return string
     */
    public function testCreateAccount($contactName)
    {
        $accountName = 'Account_'.mt_rand();

        $login = $this->login();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->assertTitle('All - Accounts - Customers')
            ->add()
            ->assertTitle('Create Account - Accounts - Customers')
            ->setName($accountName)
            ->setOwner('admin')
            ->addContact($contactName)
            ->save()
            ->assertMessage('Account saved')
            ->assertContactAdded($contactName);
    }
}
