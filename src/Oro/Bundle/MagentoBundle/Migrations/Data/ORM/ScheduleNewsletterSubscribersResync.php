<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\ChannelType;
use Oro\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;

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
                $message = new Message();
                $message->setPriority(MessagePriority::VERY_LOW);
                $message->setBody([
                    'integration_id' => $channel->getId(),
                    'connector' => InitialNewsletterSubscriberConnector::TYPE,
                    'connector_parameters' => ['skip-dictionary' => true],
                ]);

                $this->getMessageProducer()->send(Topics::SYNC_INITIAL_INTEGRATION, $message);
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
