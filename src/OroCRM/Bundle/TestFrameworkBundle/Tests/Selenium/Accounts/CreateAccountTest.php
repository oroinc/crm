<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Accounts;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Accounts;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class CreateAccountTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected function setUp()
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(intval(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT));
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
    }

    protected function tearDown()
    {
        $this->cookie()->clear();
    }

    /**
     * @return string
     */
    public function testCreateAccount()
    {
        $accountName = 'Account_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
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
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->add()
            ->setAccountName($accountName . '_autocomplete_test')
            ->setOwner('admin')
            ->setStreet('Street')
            ->setCity('City')
            ->setCountry('Kazak')
            ->setState('Aqm')
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

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
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
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->filterBy('Account name', $accountName)
            ->open(array($accountName))
            ->delete()
            ->assertTitle('Accounts - Customers')
            ->assertMessage('Account deleted');

        $login->openAccounts()
            ->filterBy('Account name', $accountName)
            ->assertNoDataMessage('No account was found to match your search');
    }
}
