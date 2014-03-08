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
            ->add()
            ->setAccountName($accountName)
            ->setOwner('admin')
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('Accounts - Customers');

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
            ->setAccountName($accountName . '_autocomplete_test')
            ->setOwner('admin')
            ->setStreet('Street')
            ->setCity('City')
            ->setCountry('Kazak')
            ->setRegion('Aqm')
            ->setZipCode('Zip Code 000')
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('Accounts - Customers');
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
            ->edit()
            ->assertTitle($accountName . ' - Edit - Accounts - Customers')
            ->setAccountName($newAccountName)
            ->save()
            ->assertMessage('Account saved')
            ->toGrid()
            ->assertTitle('Accounts - Customers')
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
            ->assertTitle('Accounts - Customers')
            ->assertMessage('Account deleted');

        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->filterBy('Account name', $accountName)
            ->assertNoDataMessage('No account was found to match your search');
    }
}
