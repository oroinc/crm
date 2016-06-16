<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Opportunities;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnels;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesHelperTrait;

/**
 * Class WorkflowTest
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales
 */
class WorkflowTest extends Selenium2TestCase
{
    use SalesHelperTrait;
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

        $leadName = $this->createLead($this->address);
        $accountName = $this->createAccount();
        $customer = $this->createB2BCustomer($accountName);

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

        $leadName = $this->createLead($this->address);
        $accountName = $this->createAccount();
        $customer = $this->createB2BCustomer($accountName);

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

        $leadName = $this->createLead($this->address);

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

        $opportunity = $this->createOpportunity();

        /** @var SalesFunnels $login */
        $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Sales Processes - Sales')
            ->startFromOpportunity()
            ->assertTitle('New Sales Process - Sales Processes')
            ->setChannel($opportunity['channel'])
            ->selectEntity('Opportunity', $opportunity['opportunity'])
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
            ->filterBy('Opportunity name', $opportunity['opportunity'])
            ->open(array($opportunity['opportunity']))
            ->checkStatus('Won');
    }

    public function testOpportunityWorkflowAsLost()
    {
        $login = $this->login();

        $opportunity = $this->createOpportunity();

        /** @var SalesFunnels $login */
        $id = $login->openSalesFunnels('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Sales Processes - Sales')
            ->startFromOpportunity()
            ->assertTitle('New Sales Process - Sales Processes')
            ->setChannel($opportunity['channel'])
            ->selectEntity('Opportunity', $opportunity['opportunity'])
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
            ->filterBy('Opportunity name', $opportunity['opportunity'])
            ->open(array($opportunity['opportunity']))
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
}
