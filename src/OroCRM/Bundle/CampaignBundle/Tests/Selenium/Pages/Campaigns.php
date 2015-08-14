<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Campaigns
 * @package OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages
 * @method Campaigns openCampaigns openCampaigns(string)
 * @method Campaign add add()
 * @method Campaign open open()
 * {@inheritdoc}
 */
class Campaigns extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Campaign']";
    const URL = 'campaign';

    public function entityNew()
    {
        return new Campaign($this->test);
    }

    public function entityView()
    {
        return new Campaign($this->test);
    }
    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }
}
