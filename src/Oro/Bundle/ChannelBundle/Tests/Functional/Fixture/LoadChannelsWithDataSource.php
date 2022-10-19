<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Tests\Functional\DataFixtures\LoadChannelData;

class LoadChannelsWithDataSource extends AbstractFixture implements DependentFixtureInterface
{
    private array $data = [
        [
            'channelReference' => 'default_channel',
            'dataSourceReference' => 'oro_integration:foo_integration',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $data) {
            /** @var Channel $channel */
            $channel = $this->getReference($data['channelReference']);
            /** @var Integration $dataSource */
            $dataSource = $this->getReference($data['dataSourceReference']);
            $channel->setDataSource($dataSource);

            $this->setReference(
                sprintf('%s_%s', $data['channelReference'], $data['dataSourceReference']),
                $channel
            );

            $manager->persist($channel);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadChannel::class, LoadChannelData::class];
    }
}
