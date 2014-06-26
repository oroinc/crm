<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Campaigns
 * @package OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages
 * @method Campaigns openCampaigns openCampaigns(string)
 * {@inheritdoc}
 */
class Campaigns extends AbstractPageFilteredGrid
{
    const URL = 'campaign';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Campaign
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Campaign']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Campaign($this->test);
    }

    /**
     * @param array $entityData
     * @return Campaign
     */
    public function open($entityData = array())
    {
        $cart = $this->getEntity($entityData, 3);
        $cart->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Campaign($this->test);
    }
}
