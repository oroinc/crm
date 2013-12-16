<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Accounts;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;

class WorkflowTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    protected $coverageScriptUrl = PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL_COVERAGE;

    protected $address = array(
        'label' => 'Address Label',
        'street' => 'Address Street',
        'city' => 'Address City',
        'zipCode' => '10001',
        'country' => 'United States',
        'state' => 'New York'
    );

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

    public function testLeadWorkflow()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();

        $leadname = $this->createLead($login);

        $login->openLeads()
            ->filterBy('Lead Name', $leadname)
            ->open(array($leadname))
            ->openWorkflow()
            ->qualify()
            ->submit()
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer need')
            ->setSolution('Some solution')
            ->submit()
            ->closeAsWon()
            ->setCloseRevenue('100')
            ->submit()
            ->openOpportunity(false)
            ->checkStatus('Won');

        return $leadname;
    }

    /**
     * @param $leadname
     * @depends testLeadWorkflow
     * @return string
     */
    public function testLeadWorkflowReactivate($leadname)
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit()
            ->openLeads()
            ->filterBy('Lead Name', $leadname)
            ->open(array($leadname))
            ->checkStatus('Qualified')
            ->reactivate()
            ->openWorkflow()
            ->disqualify()
            ->openLead()
            ->checkStatus('Canceled');
    }

    public function testOpportunityWorkflowAsWon()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();

        $opportunityName = $this->createOpportunity($login);

        $login->openOpportunities()
            ->filterBy('Opportunity Name', $opportunityName)
            ->open(array($opportunityName))
            ->openWorkflow()
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer needs')
            ->setSolution('Some solution')
            ->submit()
            ->assertTitle("B2B Sales Flow (Develop) - {$opportunityName} - Opportunities")
            ->closeAsWon()
            ->setCloseRevenue('100')
            ->submit()
            ->openOpportunity(false)
            ->checkStatus('Won');
    }

    public function testOpportunityWorkflowAsLost()
    {
        $login = new Login($this);
        $login->setUsername(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_LOGIN)
            ->setPassword(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PASS)
            ->submit();

        $opportunityName = $this->createOpportunity($login);

        $login->openOpportunities()
            ->filterBy('Opportunity Name', $opportunityName)
            ->open(array($opportunityName))
            ->checkStatus('In Progress')
            ->openWorkflow()
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer needs')
            ->setSolution('Some solution')
            ->submit()
            ->assertTitle("B2B Sales Flow (Develop) - {$opportunityName} - Opportunities")
            ->closeAsLost()
            ->setCloseReason('Cancelled')
            ->submit()
            ->openOpportunity(false)
            ->checkStatus('Lost');
    }

    /**
     * @param Login $login
     * @return string
     */
    protected function createLead($login)
    {
        $name = 'Lead_'.mt_rand();

        $login->openLeads()
            ->add()
            ->setName($name)
            ->setFirstName($name . '_first_name')
            ->setLastName($name . '_last_name')
            ->setJobTitle('Manager')
            ->setPhone('712-566-3002')
            ->setEmail($name . '@mail.com')
            ->setCompany('Some Company')
            ->setWebsite('http://www.orocrm.com')
            ->setEmployees('100')
            ->setOwner('admin')
            ->setAddress($this->address)
            ->save();

        return $name;
    }

    /**
     * @param Login $login
     * @return string
     */
    protected function createOpportunity($login)
    {
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
}
