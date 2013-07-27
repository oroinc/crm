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
        $accountname = 'Account_'.mt_rand();

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->add()
            ->setAccountName($accountname)
            ->save()
            ->assertTitle('Accounts - Customers - ORO')
            ->assertMessage('Account successfully saved');

        return $accountname;
    }

    /**
     * @depends testCreateAccount
     * @param $accountname
     */
    public function testAccountAutocmplete($accountname)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->add()
            ->setAccountName($accountname . '_autocomplete_test')
            ->setStreet('Street')
            ->setCity('City')
            ->setCountry('Kazak')
            ->setState('Aqm')
            ->setZipCode('Zip Code 000')
            ->save()
            ->assertTitle('Accounts - Customers - OR')
            ->assertMessage('Account successfully saved');
    }

    /**
     * @depends testCreateAccount
     * @param $accountname
     * @return string
     */
    public function testUpdateAccount($accountname)
    {
        $newAccountname = 'Update_' . $accountname;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->filterBy('Name', $accountname)
            ->open(array($accountname))
            ->edit()
            ->assertTitle($accountname . ' - Accounts - Customers - ORO')
            ->setAccountName($newAccountname)
            ->save()
            ->assertTitle('Accounts')
            ->assertMessage('Account successfully saved')
            ->close();

        return $newAccountname;
    }

    /**
     * @depends testUpdateContact
     * @param $accountname
     */
    public function testDeleteAccount($accountname)
    {
        $this->markTestSkipped('BAP-726');
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openAccounts()
            ->filterBy('Name', $accountname)
            ->open(array($accountname))
            ->delete()
            ->assertTitle('Accounts')
            ->assertMessage('Item was deleted');

        $login->openUsers()->filterBy('Name', $accountname)->assertNoDataMessage('No Accounts were found to match your search');
    }
}
