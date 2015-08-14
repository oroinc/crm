<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Campaigns
 * @package OroCRM\Bundle\CampaignBundle\Tests\Selenium\Pages
 * @method Campaigns openCampaigns(string $bundlePath)
 * @method Campaign add()
 * @method Campaign open(array $filter)
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
}
