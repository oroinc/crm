<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\B2BCustomers;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Opportunities;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnels;

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

    public function testLeadWorkflowAsWon()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);
        $accountName = $this->createAccount($login);
        $customer = $this->createB2BCustomer($login, $accountName);

        /** @var SalesFunnels $login */
        $id = $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Sales Processes - Sales')
            ->startFromLead()
            ->assertTitle('New Sales Process - Sales Processes')
            ->selectEntity('Lead', $leadName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Lead')
            ->qualify()
            ->setB2BCustomer($customer)
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
            ->getId();

        /** @var Opportunities $login*/
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $leadName)
            ->open(array($leadName))
            ->checkStatus('Won');

        return $id;
    }

    public function testLeadWorkflowAsLost()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);
        $accountName = $this->createAccount($login);
        $customer = $this->createB2BCustomer($login, $accountName);

        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Sales Processes - Sales')
            ->startFromLead()
            ->assertTitle('New Sales Process - Sales Processes')
            ->selectEntity('Lead', $leadName)
            ->submit()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->checkStep('New Lead')
            ->qualify()
            ->setB2BCustomer($customer)
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
            ->checkStep('Lost Opportunity');
        /** @var  Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
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
            ->filterBy('Sales', $funnelId, 'equals')
            ->open(array($funnelId))
            ->assertTitle('Sales Process #' . $funnelId . ' - Sales Processes - Sales')
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
            ->assertTitle('All - Sales Processes - Sales')
            ->startFromOpportunity()
            ->assertTitle('New Sales Process - Sales Processes')
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
            ->checkStep('Won Opportunity');
        /** @var  Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->open(array($opportunityName))
            ->checkStatus('Won');
    }

    public function testOpportunityWorkflowAsLost()
    {
        $login = $this->login();

        $opportunityName = $this->createOpportunity($login);

        /** @var SalesFunnels $login */
        $id = $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Sales Processes - Sales')
            ->startFromOpportunity()
            ->assertTitle('New Sales Process - Sales Processes')
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
            ->getId();

        /** @var Opportunities $login*/
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->open(array($opportunityName))
            ->checkStatus('Lost');

        return $id;
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
            ->filterBy('Sales', $funnelId, 'equals')
            ->open(array($funnelId))
            ->assertTitle('Sales Process #' . $funnelId . ' - Sales Processes - Sales')
            ->reopen()
            ->checkStep('New Opportunity');
    }

    /**
     * @param  Login  $login
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
     * @param  Login  $login
     * @return string
     */
    protected function createOpportunity(Login $login)
    {
        $opportunityName = 'Opportunity_'.mt_rand();
        $accountName = $this->createAccount($login);
        $customer = $this->createB2BCustomer($login, $accountName);
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($opportunityName)
            ->setB2BCustomer($customer)
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
            ->assertTitle('All - Opportunities - Sales');

        return $opportunityName;
    }

    /**
     * @param  Login  $login
     * @return string
     */
    protected function createAccount(Login $login)
    {
        $accountName = 'Account_'.mt_rand();

        /** @var Accounts $login */
        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }

    /**
     * @param Login  $login
     * @param string $account
     * @return string
     */
    protected function createB2BCustomer(Login $login, $account)
    {
        $name = 'B2BCustomer_'.mt_rand();
        /** @var B2BCustomers $login */
        $login->openB2BCustomers('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setOwner('admin')
            ->setAccount($account)
            ->save();

        return $name;
    }
}
