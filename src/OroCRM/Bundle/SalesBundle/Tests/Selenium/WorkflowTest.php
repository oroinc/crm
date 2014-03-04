<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;

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
        $activityName = 'Activity name_' . mt_rand();

        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->startFromLead()
            ->setActivityName($activityName)
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

        return $activityName;
    }

    public function testLeadWorkflowAsLost()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);
        $accountName = $this->createAccount($login);
        $activityname = 'Activity name_' . mt_rand();

        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->startFromLead()
            ->setActivityName($activityname)
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
     * @param $activityName
     * @depends testLeadWorkflowAsWon
     * @return string
     */
    public function testLeadWorkflowReopen($activityName)
    {
        $login = $this->login();
        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Name', $activityName)
            ->open(array($activityName))
            ->assertTitle($activityName . ' - Sales Activity - Sales')
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->reopen()
            ->checkStep('New Opportunity');
    }

    public function testLeadWorkflowReactivate()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);
        $activityName = 'Activity name_' . mt_rand();

        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->startFromLead()
            ->setActivityName($activityName)
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
        $activityName = 'Activity name_' . mt_rand();

        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->startFromOpportunity()
            ->setActivityName($activityName)
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
        $activityName = 'Activity name_' . mt_rand();

        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->startFromOpportunity()
            ->setActivityName($activityName)
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

        return $activityName;
    }

    /**
     * @param $activityName
     * @depends testOpportunityWorkflowAsLost
     * @return string
     */
    public function testOpportunityWorkflowReopen($activityName)
    {
        $login = $this->login();
        $login->openSalesActivities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Name', $activityName)
            ->open(array($activityName))
            ->assertTitle($activityName . ' - Sales Activity - Sales')
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
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

        $login->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setAccountName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }
}
