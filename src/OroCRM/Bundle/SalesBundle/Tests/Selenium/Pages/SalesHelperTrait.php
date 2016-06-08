<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages\Accounts;
use OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;

trait SalesHelperTrait
{
    /**
     * @param array $address
     * @return string
     */
    protected function createLead($address)
    {
        $name = 'Lead_' . mt_rand();
        /** @var Leads $page */
        $page = new Leads($this, false);
        $page->openLeads('OroCRM\Bundle\SalesBundle')
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
            ->setAddress($address)
            ->save();

        return $name;
    }

    /**
     * @return string
     */
    public function createChannel()
    {
        $name = 'Channel_' . mt_rand();

        /** @var Channels $page */
        $page = new Channels($this, false);
        $page->openChannels('OroCRM\Bundle\ChannelBundle')
            ->assertTitle('All - Channels - System')
            ->add()
            ->assertTitle('Create Channel - Channels - System')
            ->setType('Sales')
            ->setName($name)
            ->setStatus('Active')
            ->save()
            ->assertMessage('Channel saved');

        return $name;
    }


    /**
     * @return array
     */
    protected function createOpportunity()
    {
        $opportunityName = 'Opportunity_'.mt_rand();
        $channelName = $this->createChannel();
        $accountName = $this->createAccount();
        $customer = $this->createB2BCustomer($accountName, $channelName);

        /** @var Opportunities $page */
        $page = new Opportunities($this, false);
        $page->openOpportunities('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($opportunityName)
            ->setChannel($channelName)
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

        return [
            'opportunity' => $opportunityName,
            'channel' => $channelName,
            'customer' => $customer,
            'account' => $accountName
        ];
    }

    /**
     * @return string
     */
    protected function createAccount()
    {
        $accountName = 'Account_'.mt_rand();

        /** @var Accounts $page */
        $page = new Accounts($this, false);
        $page->openAccounts('OroCRM\Bundle\AccountBundle')
            ->add()
            ->setName($accountName)
            ->setOwner('admin')
            ->save();

        return $accountName;
    }

    /**
     * @param string $account
     * @param string $channel
     * @return string
     */
    protected function createB2BCustomer($account, $channel = '')
    {
        $name = 'B2BCustomer_'.mt_rand();
        /** @var B2BCustomers $page */
        $page = new B2BCustomers($this, false);
        $page->openB2BCustomers('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setOwner('admin')
            ->setAccount($account)
            ->save();

        return $name;
    }
}
