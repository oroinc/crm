<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Behat\Context;

use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;

class FeatureContext extends OroFeatureContext implements FixtureLoaderAwareInterface
{
    use FixtureLoaderDictionary;

    /**
     * @Given /^"(?P<channelName>([\w\s]+))" is a channel with enabled (?P<entities>(.+)) entities$/
     */
    public function isAChannelWithEnabledOpportunityLeadBusinessCustomerEntities($channelName, $entities)
    {
        $this->fixtureLoader->loadFixtureFile('sales_channel.yml');
    }
}
