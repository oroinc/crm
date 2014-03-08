<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;

/**
 * Class CreateOpportunityTest
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales
 */
class CreateOpportunityTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateOpportunity()
    {
        $login = $this->login();

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

    /**
     * @depends testCreateOpportunity
     * @param $name
     * @return string
     */
    public function testUpdateOpportunity($name)
    {
        $newName = 'Update_' . $name;

        $login = $this->login();
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
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
        $login = $this->login();
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $name)
            ->open(array($name))
            ->delete()
            ->assertTitle('Opportunities - Sales')
            ->assertMessage('Opportunity deleted')
            ->assertNoDataMessage('No records found');
    }
}
