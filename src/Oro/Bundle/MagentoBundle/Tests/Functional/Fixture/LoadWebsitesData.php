<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Entity\Website;

class LoadWebsitesData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadMagentoChannel::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Channel $channel */
        $channel = $this->getReference('integration');
        $website = new Website();
        $website->setName('website2');
        $website->setOriginId(2);
        $website->setCode('ws2');
        $website->setChannel($channel);

        $this->setReference('magento.website2', $website);
        $manager->persist($website);

        $store = new Store;
        $store->setName('ws2 store');
        $store->setChannel($channel);
        $store->setCode('ws2_store1');
        $store->setWebsite($website);
        $store->setOriginId(2);

        $manager->persist($store);
        $this->setReference('magento.website2.store', $store);

        $manager->flush();
    }
}
