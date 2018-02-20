<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
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
        /** @var IntegrationRepository $integrationRepository */
        $integrationRepository = $manager->getRepository(Integration::class);
        /** @var Integration[] $applicableIntegrations */
        /**
         * @todo Remove dependency on exact magento channel type in CRM-8156
         */
        $applicableIntegrations = $integrationRepository->getConfiguredChannelsForSync(MagentoChannelType::TYPE);
        if ($applicableIntegrations) {
            foreach ($applicableIntegrations as $integration) {
                $this->getMessageProducer()->send(
                    Topics::SYNC_INITIAL_INTEGRATION,
                    new Message(
                        [
                            'integration_id'       => $integration->getId(),
                            'connector'            => InitialNewsletterSubscriberConnector::TYPE,
                            'connector_parameters' => ['skip-dictionary' => true],
                        ],
                        MessagePriority::VERY_LOW
                    )
                );
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
