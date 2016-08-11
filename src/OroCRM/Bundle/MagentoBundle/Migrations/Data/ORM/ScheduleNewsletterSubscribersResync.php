<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use OroCRM\Bundle\MagentoBundle\Async\Topics;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ScheduleNewsletterSubscribersResync implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

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
                $this->getMessageProducer()->send(Topics::SYNC_INITIAL_INTEGRATION, [
                    'integration_id' => $channel->getId(),
                    'connector' => InitialNewsletterSubscriberConnector::TYPE,
                    'connector_parameters' => ['skip-dictionary' => true],
                ], MessagePriority::VERY_LOW);
            }
        }
    }

    /**
     * @return MessageProducerInterface
     */
    private function getMessageProducer()
    {
        return $this->container->get('oro_message_queue.message_producer');
    }
}
