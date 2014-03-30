<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Opportunities;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnel;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnels;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Workflow;

/**
 * Class WorkflowTest
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales
 */
class WorkflowTest extends Selenium2TestCase
{
     protected $address = array(
        'label' => 'Address Label',
        'street' => 'Address Street',
        'city' => 'Address City',
        'zipCode' => '10001',
        'country' => 'United States',
        'region' => 'New York'
    );

    protected function setUp()
    {
        $this->markTestIncomplete('Update tests according to changed funnel structure (CRM-916)');
    }

    public function testLeadWorkflowAsWon()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);
        $accountName = $this->createAccount($login);

        /** @var SalesFunnels $login */
        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->startFromLead()
            ->selectEntity('Lead', $leadName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Lead')
            ->qualify()
            ->setAccount($accountName)
            ->submit()
            ->checkStep('New Opportunity')
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer need')
            ->setSolution('Some solution')
            ->submit()
            ->checkStep('Developed Opportunity')
            ->closeAsWon()
            ->setCloseRevenue('100')
            ->submit()
            ->checkStep('Won Opportunity')
            ->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $leadName)
            ->open(array($leadName))
            ->checkStatus('Won');

        // TODO: return sales funnel ID (CRM-916)
    }

    public function testLeadWorkflowAsLost()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);
        $accountName = $this->createAccount($login);

        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->startFromLead()
            ->selectEntity('Lead', $leadName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Lead')
            ->qualify()
            ->setAccount($accountName)
            ->submit()
            ->checkStep('New Opportunity')
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer need')
            ->setSolution('Some solution')
            ->submit()
            ->checkStep('Developed Opportunity')
            ->closeAsLost()
            ->setCloseReason('Cancelled')
            ->submit()
            ->checkStep('Lost Opportunity')
            ->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $leadName)
            ->open(array($leadName))
            ->checkStatus('Lost');
    }

    /**
     * @param $funnelId
     * @depends testLeadWorkflowAsWon
     * @return string
     */
    public function testLeadWorkflowReopen($funnelId)
    {
        /** @var SalesFunnels $login */
        $login = $this->login();
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->filterBy('Name', $funnelId)
            ->open(array($funnelId))
            ->assertTitle($funnelId . ' - Sales Processes - Sales')
            ->reopen()
            ->checkStep('New Opportunity');
    }

    public function testLeadWorkflowReactivate()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);

        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->startFromLead()
            ->selectEntity('Lead', $leadName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Lead')
            ->disqualify()
            ->checkStep('Disqualified Lead')
            ->reactivate()
            ->checkStep('New Lead');
    }

    public function testOpportunityWorkflowAsWon()
    {
        $login = $this->login();

        $opportunityName = $this->createOpportunity($login);

        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->startFromOpportunity()
            ->selectEntity('Opportunity', $opportunityName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Opportunity')
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer need')
            ->setSolution('Some solution')
            ->submit()
            ->checkStep('Developed Opportunity')
            ->closeAsWon()
            ->setCloseRevenue('100')
            ->submit()
            ->checkStep('Won Opportunity')
            ->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->open(array($opportunityName))
            ->checkStatus('Won');
    }

    public function testOpportunityWorkflowAsLost()
    {
        $login = $this->login();

        $opportunityName = $this->createOpportunity($login);

        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->startFromOpportunity()
            ->selectEntity('Opportunity', $opportunityName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Opportunity')
            ->develop()
            ->setBudget('100')
            ->setProbability('100')
            ->setCustomerNeed('Some customer need')
            ->setSolution('Some solution')
            ->submit()
            ->checkStep('Developed Opportunity')
            ->closeAsLost()
            ->setCloseReason('Cancelled')
            ->submit()
            ->checkStep('Lost Opportunity')
            ->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->open(array($opportunityName))
            ->checkStatus('Lost');

        // TODO: return sales funnel ID (CRM-916)
    }

    /**
     * @param $funnelId
     * @depends testOpportunityWorkflowAsLost
     * @return string
     */
    public function testOpportunityWorkflowReopen($funnelId)
    {
        $login = $this->login();
        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->filterBy('Name', $funnelId)
            ->open(array($funnelId))
            ->assertTitle($funnelId . ' - Sales Processes - Sales')
            ->reopen()
            ->checkStep('New Opportunity');
    }

    /**
     * @param Login $login
     * @return string
     */
    protected function createLead(Login $login)
    {
        $name = 'Lead_'.mt_rand();

        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
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
    protected function createOpportunity(Login $login)
    {
        $opportunityName = 'Opportunity_'.mt_rand();
        $accountName = $this->createAccount($login);

        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
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

        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setAccountName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }
}
