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

    public function testLeadWorkflow()
    {
        $login = $this->login();

        $leadName = $this->createLead($login);

        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $leadName)
            ->open(array($leadName))
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
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
            ->openOpportunity('OroCRM\Bundle\SalesBundle', false)
            ->checkStatus('Won');

        return $leadName;
    }

    /**
     * @param $leadName
     * @depends testLeadWorkflow
     * @return string
     */
    public function testLeadWorkflowReactivate($leadName)
    {
        $login = $this->login();
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $leadName)
            ->open(array($leadName))
            ->checkStatus('Qualified')
            ->reactivate()
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
            ->disqualify()
            ->openLead('OroCRM\Bundle\SalesBundle')
            ->checkStatus('Canceled');
    }

    public function testOpportunityWorkflowAsWon()
    {
        $login = $this->login();

        $opportunityName = $this->createOpportunity($login);

        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->open(array($opportunityName))
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
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
            ->openOpportunity('OroCRM\Bundle\SalesBundle', false)
            ->checkStatus('Won');
    }

    public function testOpportunityWorkflowAsLost()
    {
        $login = $this->login();

        $opportunityName = $this->createOpportunity($login);

        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $opportunityName)
            ->open(array($opportunityName))
            ->checkStatus('In Progress')
            ->openWorkflow('OroCRM\Bundle\SalesBundle')
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
            ->openOpportunity('OroCRM\Bundle\SalesBundle', false)
            ->checkStatus('Lost');
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
