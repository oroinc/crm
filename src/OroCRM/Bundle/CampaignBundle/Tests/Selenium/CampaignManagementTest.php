<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Selenium;

use Oro\Bundle\ReportBundle\Tests\Selenium\Pages\Reports;
use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages\Campaigns;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\Leads;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnel;
use OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnels;

/**
 * Class Campaign Test
 *
 * @package OroCRM\Bundle\CampaignBundle\Tests\Selenium
 * {@inheritdoc}
 */
class CampaignManagementTest extends Selenium2TestCase
{

    /**
     * @return string
     */
    public function testCreateCampaign()
    {
        $campaignCode = 'Campaign_' . mt_rand();

        $login = $this->login();
        /** @var Campaigns $login */
        $login->openCampaigns('OroCRM\Bundle\CampaignBundle')
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
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($leadName)
            ->setFirstName($leadName)
            ->setLastName($leadName)
            ->setCampaign($campaign)
            ->save()
            ->assertMessage('Lead saved');

        return $leadName;
    }

    public function testCreateSales()
    {
        $leadName = 'Lead_' . mt_rand();

        $login = $this->login();
        /** @var SalesFunnels $login */
        //$login->openSalesFunnels('OroCRM\Bundle\SalesBundle')


    }
    /**
     * @depends testCreateCampaign
     * @param string $campaignCode
     *
     */
    public function testCheckReport($campaignCode)
    {
        $login = $this->login();
        /** @var Reports $login */
        $login = $login->openReports('Oro\Bundle\ReportBundle')
            ->open(array('Campaign Performance'))
            ->filterBy('Code', $campaignCode);
        $rows = $login->getRows();
        $data = $login->getData($rows);
    }
}
