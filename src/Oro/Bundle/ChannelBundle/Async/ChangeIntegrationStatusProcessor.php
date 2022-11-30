<?php

namespace Oro\Bundle\ChannelBundle\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\Topic\ChannelStatusChangedTopic;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Utils\EditModeUtils;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Changes channel data source.
 * Clears channels cache.
 */
class ChangeIntegrationStatusProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ManagerRegistry $registry;

    private StateProvider $stateProvider;

    public function __construct(ManagerRegistry $registry, StateProvider $stateProvider)
    {
        $this->registry = $registry;
        $this->stateProvider = $stateProvider;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();

        $entityManager = $this->registry->getManagerForClass(Channel::class);

        $channel = $entityManager->find(Channel::class, $messageBody['channelId']);
        if (!$channel) {
            $this->logger->critical(sprintf('Channel not found: %d', $messageBody['channelId']));

            return self::REJECT;
        }

        $dataSource = $channel->getDataSource();
        if ($dataSource instanceof Integration) {
            if (Channel::STATUS_ACTIVE === $channel->getStatus()) {
                $dataSource->setEnabled($dataSource->getPreviouslyEnabled() ?? true);

                EditModeUtils::attemptChangeEditMode($dataSource, Integration::EDIT_MODE_RESTRICTED);
            } else {
                $dataSource->setPreviouslyEnabled($dataSource->isEnabled());
                $dataSource->setEnabled(false);

                EditModeUtils::attemptChangeEditMode($dataSource, Integration::EDIT_MODE_DISALLOW);
            }

            $entityManager->persist($dataSource);
            $entityManager->flush();
        }

        $this->stateProvider->processChannelChange();

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [ChannelStatusChangedTopic::getName()];
    }
}
