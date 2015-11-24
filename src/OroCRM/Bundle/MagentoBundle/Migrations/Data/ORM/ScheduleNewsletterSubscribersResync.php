<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;
use OroCRM\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class ScheduleNewsletterSubscribersResync implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var ChannelRepository $channelRepository */
        $channelRepository = $manager->getRepository('OroIntegrationBundle:Channel');
        /** @var Channel[] $applicableChannels */
        $applicableChannels = $channelRepository->getConfiguredChannelsForSync(ChannelType::TYPE);
        if ($applicableChannels) {
            foreach ($applicableChannels as $channel) {
                $job = new Job(
                    InitialSyncCommand::COMMAND_NAME,
                    [
                        sprintf('--integration-id=%s', $channel->getId()),
                        sprintf('--connector=%s', InitialNewsletterSubscriberConnector::TYPE),
                        '--skip-dictionary',
                        '-v'
                    ]
                );
                $manager->persist($job);
            }
            $manager->flush();
        }
    }
}
