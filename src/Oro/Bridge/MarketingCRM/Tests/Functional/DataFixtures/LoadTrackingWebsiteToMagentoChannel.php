<?php

namespace Oro\Bridge\MarketingCRM\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Tests\Functional\DataFixtures\LoadTrackingWebsites;

class LoadTrackingWebsiteToMagentoChannel extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadTrackingWebsites::class,
            LoadMagentoChannel::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var TrackingWebsite $website */
        $website = $this->getReference(LoadTrackingWebsites::TRACKING_WEBSITE);

        if (method_exists($website, 'setChannel')) {
            /** @var Channel $channel */
            $channel = $this->getReference('default_channel');
            $website->setChannel($channel);
            $manager->flush($website);
        }
    }
}
