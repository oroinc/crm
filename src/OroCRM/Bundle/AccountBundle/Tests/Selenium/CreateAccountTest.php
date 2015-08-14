<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Accounts;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;

/**
 * Class CreateAccountTest
 *
 * @package OroCRM\Bundle\AccountBundle\Tests\Selenium\Accounts
 */
class CreateAccountTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateAccount()
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
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('All - Accounts - Customers');

        return $accountName;
    }

    /**
     * @depends testCreateAccount
     * @param $accountName
     */
    public function testAccountAutocomplete($accountName)
    {
        $login = $this->login();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setName($accountName . '_autocomplete_test')
            ->setOwner('admin')
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('All - Accounts - Customers');
    }

    /**
     * @depends testCreateAccount
     * @param $accountName
     * @return string
     */
    public function testUpdateAccount($accountName)
    {
        $newAccountName = 'Update_' . $accountName;

        $login = $this->login();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->filterBy('Account name', $accountName)
            ->open(array($accountName))
            ->assertTitle("{$accountName} - Accounts - Customers")
            ->edit()
            ->assertTitle("{$accountName} - Edit - Accounts - Customers")
            ->setName($newAccountName)
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('All - Accounts - Customers')
            ->close();
         return $newAccountName;
    }

    /**
     * @depends testUpdateAccount
     * @param $accountName
     */
    public function testDeleteAccount($accountName)
    {
        $login = $this->login();
        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->filterBy('Account name', $accountName)
            ->open(array($accountName))
            ->delete()
            ->assertMessage('Account deleted')
            ->assertTitle('All - Accounts - Customers');


        $login->openAccounts('OroCRM\Bundle\AccountBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Account name', $accountName)
                ->assertNoDataMessage('No account was found to match your search');
        }
    }
}
