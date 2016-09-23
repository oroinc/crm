<?php

namespace Oro\Bundle\CampaignBundle\Tests\Selenium;

use Oro\Bundle\ReportBundle\Tests\Selenium\Pages\Reports;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\CampaignBundle\Tests\Selenium\Pages\Campaigns;
use Oro\Bundle\ChannelBundle\Tests\Selenium\Pages\Channels;
use Oro\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;
use Oro\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnels;

/**
 * Class Campaign Test
 *
 * @package Oro\Bundle\CampaignBundle\Tests\Selenium
 * {@inheritdoc}
 */
class CampaignManagementTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateChannel()
    {
        $name = 'Channel_' . mt_rand();

        $login = $this->login();
        /** @var Channels $login */
        $login->openChannels('Oro\Bundle\ChannelBundle')
            ->assertTitle('All - Channels - System')
            ->add()
            ->assertTitle('Create Channel - Channels - System')
            ->setType('Custom')
            ->setName($name)
            ->setStatus('Active')
            ->addEntity('Opportunity')
            ->addEntity('Lead')
            ->addEntity('Sales Process')
            ->addEntity('Business Customer')
            ->save()
            ->assertMessage('Channel saved');

        return $name;
    }

    /**
     * Test create new campaign functionality
     * @return string
     */
    public function testCreateCampaign()
    {
        $campaignCode = 'Campaign_' . mt_rand();

        $login = $this->login();
        /** @var Campaigns $login */
        $login->openCampaigns('Oro\Bundle\CampaignBundle')
            ->add()
            ->assertTitle('Create Campaign - Campaigns - Marketing')
            ->setName($campaignCode)
            ->setCode($campaignCode)
            ->save()
            ->assertMessage('Campaign saved')
            ->assertTitle("{$campaignCode} - Campaigns - Marketing");

        return $campaignCode;
    }

    /**
     * Test create new lead with company assignment
     * @depends testCreateCampaign
     * @param string $campaign
     *
     * @return string
     */
    public function testCreateLead($campaign)
    {
        $leadName = 'Lead_' . mt_rand();

        $login = $this->login();
        /** @var Leads $login */
        $login->openLeads('Oro\Bundle\SalesBundle')
            ->add()
            ->setName($leadName)
            ->setFirstName($leadName)
            ->setLastName($leadName)
            ->setCampaign($campaign)
            ->save()
            ->assertMessage('Lead saved');

        return $leadName;
    }

    /**
     * Test create new sales activity with lead assigned company
     * @depends testCreateLead
     * @param $leadName
     */
    public function testCreateCompanySales($leadName)
    {

        $login = $this->login();
        /** @var SalesFunnels $login */
        $login->openSalesFunnels('Oro\Bundle\SalesBundle')
            ->startFromLead()
            ->selectEntity('Lead', $leadName)
            ->submit()
            ->openWorkflow('Oro\Bundle\SalesBundle')
            ->checkStep('New Lead')
            ->qualify()
            ->setCompanyName('Test company name_'.mt_rand())
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
            ->checkStep('Won Opportunity');
    }

    /**
     * Test report on active company
     * @depends testCreateCampaign
     * @depends testCreateCompanySales
     * @param string $campaignCode
     */
    public function testCheckReport($campaignCode)
    {
        $login = $this->login();
        /** @var Reports $login */
        $data = $login = $login->openReports('Oro\Bundle\ReportBundle')
            ->open(array('Campaign Performance'))
            ->filterBy('Code', $campaignCode)
            ->getAllData();

        static::assertEquals('$100.00', $data[0]['CLOSE REVENUE']);
    }
}
