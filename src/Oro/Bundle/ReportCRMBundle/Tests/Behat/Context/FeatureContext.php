<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Behat\Context;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;

class FeatureContext extends OroFeatureContext implements FixtureLoaderAwareInterface
{
    use FixtureLoaderDictionary;

    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

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
        $manager = $this->managerRegistry->getManagerForClass(Lead::class);

        return $manager->getClassMetadata(Lead::class);
    }
}
