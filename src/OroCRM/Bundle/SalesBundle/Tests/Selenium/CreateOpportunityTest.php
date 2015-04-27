<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\B2BCustomers;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Opportunities;

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
        $customer = $this->createB2BCustomer($login, $accountName);
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->assertTitle('Opportunities - Sales')
            ->add()
            ->assertTitle('Create Opportunity - Opportunities - Sales')
            ->setName($opportunityName)
            ->setProbability('50')
            ->seBudget('100')
            ->setCustomerNeed('50')
            ->setProposedSolution('150')
            ->setCloseRevenue('200')
            ->setCloseDate('Sep 26, 2013')
            ->setOwner('admin')
            ->setB2BCustomer($customer)
            ->save()
            ->assertMessage('Opportunity saved')
            ->toGrid()
            ->assertTitle('Opportunities - Sales');

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
            ->setAccountName($accountName)
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

    /**
     * @depends testCreateOpportunity
     * @param $name
     * @return string
     */
    public function testUpdateOpportunity($name)
    {
        $newName = 'Update_' . $name;

        $login = $this->login();
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $name)
            ->open(array($name))
            ->assertTitle("{$name} - Opportunities - Sales")
            ->edit()
            ->assertTitle("{$name} - Edit - Opportunities - Sales")
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
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->filterBy('Opportunity name', $name)
            ->open(array($name))
            ->delete()
            ->assertTitle('Opportunities - Sales')
            ->assertMessage('Opportunity deleted')
            ->assertNoDataMessage('No records found');
    }
}
