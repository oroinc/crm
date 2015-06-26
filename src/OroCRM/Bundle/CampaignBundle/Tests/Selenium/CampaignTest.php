<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages\Campaigns;

/**
 * Class Campaign Test
 *
 * @package OroCRM\Bundle\CampaignBundle\Tests\Selenium
 * {@inheritdoc}
 */
class CampaignTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreate()
    {
        $campaignCode = 'Campaign_' . mt_rand();

        $login = $this->login();
        /** @var Campaigns $login */
        $login->openCampaigns('OroCRM\Bundle\CampaignBundle')
            ->assertTitle('All - Campaigns - Marketing')
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
     * @depends testCreate
     * @param $campaignCode
     * @return string
     */
    public function testUpdate($campaignCode)
    {
        $newСampaignCode = substr('Update_' . $campaignCode, 0, 20);

        $login = $this->login();
        /** @var Campaigns $login */
        $login->openCampaigns('OroCRM\Bundle\CampaignBundle')
            ->filterBy('Code', $campaignCode)
            ->open(array($campaignCode))
            ->assertTitle("{$campaignCode} - Campaigns - Marketing")
            ->edit()
            ->assertTitle($campaignCode . ' - Edit - Campaigns - Marketing')
            ->setCode($newСampaignCode)
            ->save()
            ->assertMessage('Campaign saved')
            ->assertTitle("{$campaignCode} - Campaigns - Marketing")
            ->close();

        return $newСampaignCode;
    }

    /**
     * @depends testUpdate
     * @param $campaignCode
     */
    public function testDelete($campaignCode)
    {
        $login = $this->login();
        /** @var Campaigns $login */
        $login->openCampaigns('OroCRM\Bundle\CampaignBundle')
            ->filterBy('Code', $campaignCode)
            ->delete(array($campaignCode))
            ->assertMessage('Item deleted')
            ->assertTitle('All - Campaigns - Marketing');

        /** @var Campaigns $login */
        $login = $login->openCampaigns('OroCRM\Bundle\CampaignBundle');
        if ($login->getRowsCount() > 0) {
            $login->filterBy('Code', $campaignCode)
                ->assertNoDataMessage('No entity was found to match your search');
        }
    }
}
