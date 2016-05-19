<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;
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
        $channelName = $this->createChannel($login);
        $customer = $this->createB2BCustomer($login, $accountName, $channelName);
        /** @var Opportunities $login */
        $login->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Opportunities - Sales')
            ->add()
            ->assertTitle('Create Opportunity - Opportunities - Sales')
            ->setName($opportunityName)
            ->setChannel($channelName)
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
            ->assertTitle('All - Opportunities - Sales');

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
            ->setName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }

    /**
     * @param Login $login
     * @return string
     */
    protected function createChannel(Login $login)
    {
        $channelName = 'Channel_'.mt_rand();
        /** @var Channels $login */
        $login->openChannels('OroCRM\Bundle\ChannelBundle')
            ->assertTitle('All - Channels - System')
            ->add()
            ->assertTitle('Create Channel - Channels - System')
            ->setType('Sales')
            ->setName($channelName)
            ->setStatus('Active')
            ->save()
            ->assertMessage('Channel saved');

        return $channelName;
    }

    /**
     * @param Login $login
     * @param string $account
     * @param string $channel
     * @return string
     */
    protected function createB2BCustomer(Login $login, $account, $channel)
    {
        $name = 'B2BCustomer_'.mt_rand();
        /** @var B2BCustomers $login */
        $login->openB2BCustomers('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setOwner('admin')
            ->setAccount($account)
            ->setChannel($channel)
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
            ->assertTitle('All - Opportunities - Sales')
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
            ->assertMessage('Opportunity deleted')
            ->assertTitle('All - Opportunities - Sales')
            ->assertNoDataMessage('No records found');
    }
}
