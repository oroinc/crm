<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

class CreateOpportunityTest extends Selenium2TestCase
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
    public function testCreateOpportunity()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();

        $opportunityName = 'Opportunity_'.mt_rand();
        $accountName = $this->createAccount($login);

        $login->openOpportunities()
            ->add()
            ->setName($opportunityName)
            ->setAccount($accountName)
            ->setProbability('50')
            ->seBudget('100')
            ->setCustomerNeed('50')
            ->setProposedSolution('150')
            ->setCloseRevenue('200')
            ->setCloseDate('Sep 26, 2013')
            ->setOwner('admin')
            ->save()
            ->assertMessage('Opportunity saved')
            ->toGrid()
            ->assertTitle('Opportunities - Sales');

        return $opportunityName;
    }

    /**
     * @param Login $login
     * @return string
     */
    protected function createAccount(Login $login)
    {
        $accountName = 'Account_'.mt_rand();

        $login->openAccounts()
            ->add()
            ->setAccountName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }

    /**
     * @depends testCreateOpportunity
     * @param $name
     * @return string
     */
    public function testUpdateOpportunity($name)
    {
        $newName = 'Update_' . $name;

        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openOpportunities()
            ->filterBy('Opportunity name', $name)
            ->open(array($name))
            ->edit()
            ->assertTitle($name . ' - Edit - Opportunities - Sales')
            ->setName($newName)
            ->save()
            ->assertMessage('Opportunity saved')
            ->toGrid()
            ->assertTitle('Opportunities - Sales')
            ->close();

        return $newName;
    }

    /**
     * @depends testUpdateOpportunity
     * @param $name
     */
    public function testDeleteOpportunity($name)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openOpportunities()
            ->filterBy('Opportunity name', $name)
            ->open(array($name))
            ->delete()
            ->assertTitle('Opportunities - Sales')
            ->assertMessage('Opportunity deleted')
            ->assertNoDataMessage('No opportunities exists');
    }
}
