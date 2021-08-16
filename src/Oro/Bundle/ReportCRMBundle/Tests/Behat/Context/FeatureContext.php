<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Behat\Context;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;

class FeatureContext extends OroFeatureContext implements FixtureLoaderAwareInterface
{
    use FixtureLoaderDictionary;

    /**
     * Load "LeadsByDateReportFixture.yml" alice fixture from ReportCRMBundle suite
     *
     * PrePersist lifecycleCallback will override createdAt and updatedAt fields passed from fixture.
     * So, we should disable this callback to save original values.
     *
     * @Given /^leads by date report fixture loaded$/
     */
    public function bestSellingFixtureLoaded()
    {
        $metadata = $this->getMetadata();

        $events = $metadata->lifecycleCallbacks;
        $metadata->setLifecycleCallbacks([]);

        $this->fixtureLoader->loadFixtureFile('OroReportCRMBundle:LeadsByDateReportFixture.yml');

        $metadata->setLifecycleCallbacks($events);
    }

    /**
     * @return ClassMetadataInfo
     */
    private function getMetadata()
    {
        $manager = $this->getAppContainer()->get('doctrine')->getManagerForClass(Lead::class);

        return $manager->getClassMetadata(Lead::class);
    }
}
