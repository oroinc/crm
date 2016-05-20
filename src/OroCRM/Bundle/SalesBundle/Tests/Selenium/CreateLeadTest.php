<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;

/**
 * Class CreateLeadTest
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales
 */
class CreateLeadTest extends Selenium2TestCase
{
    protected $address = array(
        'label' => 'Address Label',
        'street' => 'Address Street',
        'city' => 'Address City',
        'zipCode' => '10001',
        'country' => 'United States',
        'region' => 'New York'
    );

    /**
     * @return string
     */
    public function testCreateLead()
    {
        $name = 'Lead_'.mt_rand();
        $login = $this->login();
        $channelName = $this->createChannel($login);
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->assertTitle('All - Leads - Sales')
            ->add()
            ->assertTitle('Create Lead - Leads - Sales')
            ->setName($name)
            ->setChannel($channelName)
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
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('All - Leads - Sales');

        return $name;
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
     * @depends testCreateLead
     * @param $name
     * @return string
     */
    public function testUpdateLead($name)
    {
        $newName = 'Update_' . $name;

        $login = $this->login();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $name)
            ->open(array($name))
            ->assertTitle("{$name} - Leads - Sales")
            ->edit()
            ->assertTitle("{$name} - Edit - Leads - Sales")
            ->setName($newName)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('All - Leads - Sales')
            ->close();

        return $newName;
    }

    /**
     * @depends testUpdateLead
     * @param $name
     */
    public function testDeleteLead($name)
    {
        $login = $this->login();
        /** @var Leads $login */
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $name)
            ->open(array($name))
            ->delete()
            ->assertMessage('Lead deleted')
            ->assertTitle('All - Leads - Sales')
            ->assertNoDataMessage('No records found');
    }
}
