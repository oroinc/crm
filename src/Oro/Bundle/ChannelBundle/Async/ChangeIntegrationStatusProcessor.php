<?php

namespace Oro\Bundle\ChannelBundle\Async;

use Oro\Bundle\IntegrationBundle\Utils\EditModeUtils;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ChangeIntegrationStatusProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = array_replace(['channelId' => null], JSON::decode($message->getBody()));
        if (false == $body['channelId']) {
            throw new \LogicException('The message invalid. It must have channelId set');
        }

        $em = $this->registry->getManager();

        /** @var Channel $channel */
        $channel = $em->find(Channel::class, $body['channelId']);
        if (false == $channel) {
            return self::REJECT;
        }

        $dataSource = $channel->getDataSource();
        if ($dataSource instanceof Integration) {
            if (Channel::STATUS_ACTIVE === $channel->getStatus()) {
                $enabled = null !== $dataSource->getPreviouslyEnabled() ?
                    $dataSource->getPreviouslyEnabled() :
                    true;

                $dataSource->setEnabled($enabled);
                EditModeUtils::attemptChangeEditMode($dataSource, Integration::EDIT_MODE_RESTRICTED);
            } else {
                $dataSource->setPreviouslyEnabled($dataSource->isEnabled());
                $dataSource->setEnabled(false);
                EditModeUtils::attemptChangeEditMode($dataSource, Integration::EDIT_MODE_DISALLOW);
            }

            $em->persist($dataSource);
            $em->flush();
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::CHANNEL_STATUS_CHANGED];
    }
}
