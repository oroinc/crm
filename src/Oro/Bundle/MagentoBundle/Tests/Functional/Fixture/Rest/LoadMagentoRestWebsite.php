<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\Rest;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Website;

class LoadMagentoRestWebsite extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadMagentoRestChannel::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminWebsite = new Website();
        $adminWebsite
            ->setName('Admin website')
            ->setCode('admin')
            ->setOriginId(0)
            ->setDefaultGroupId(0)
            ->setChannel($this->getReference('default_integration_channel'));

        $manager->persist($adminWebsite);

        $baseWebsite = new Website();
        $baseWebsite
            ->setName('Main website')
            ->setCode('base')
            ->setOriginId(1)
            ->setDefaultGroupId(1)
            ->setChannel($this->getReference('default_integration_channel'));

        $manager->persist($baseWebsite);
        $manager->flush();

        /**
         * @var MagentoTransport $transport
         */
        $transport = $this->getReference('default_transport');
        $transport
            ->setWebsites(
                [
                    [
                        'id' => $adminWebsite->getId(),
                        'label' => 'Admin website',
                    ],
                    [
                        'id' => $baseWebsite->getId(),
                        'label' => 'Main website',
                    ],
                ]
            )
            ->setWebsiteId($baseWebsite->getId());
        $manager->flush();
    }
}
