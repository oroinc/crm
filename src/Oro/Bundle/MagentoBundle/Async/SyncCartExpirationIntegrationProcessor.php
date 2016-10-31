<?php
namespace Oro\Bundle\MagentoBundle\Async;

use Psr\Log\LoggerInterface;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;

class SyncCartExpirationIntegrationProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var CartExpirationProcessor
     */
    private $cartExpirationProcessor;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RegistryInterface $doctrine
     * @param CartExpirationProcessor $cartExpirationProcessor
     * @param JobRunner $jobRunner
     * @param LoggerInterface $logger
     */
    public function __construct(
        RegistryInterface $doctrine,
        CartExpirationProcessor $cartExpirationProcessor,
        JobRunner $jobRunner,
        LoggerInterface $logger
    ) {
        $this->doctrine = $doctrine;
        $this->cartExpirationProcessor = $cartExpirationProcessor;
        $this->jobRunner = $jobRunner;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SYNC_CART_EXPIRATION_INTEGRATION];
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integrationId' => null,
        ], $body);

        if (! $body['integrationId']) {
            $this->logger->critical('The message invalid. It must have integrationId set', ['message' => $message]);

            return self::REJECT;
        }

        $ownerId = $message->getMessageId();
        $jobName = 'oro_magento:sync_cart_expiration_integration:'.$body['integrationId'];

        /** @var ChannelRepository $repository */
        $repository = $this->doctrine->getRepository(Channel::class);
        $channel = $repository->getOrLoadById($body['integrationId']);

        if (! $channel || ! $channel->isEnabled()) {
            $this->logger->critical(
                sprintf('The channel should exist and be enabled: %s', $body['integrationId']),
                ['message' => $message]
            );

            return self::REJECT;
        }

        if (! is_array($channel->getConnectors()) || ! in_array('cart', $channel->getConnectors())) {
            $this->logger->critical(
                sprintf('The channel should have cart in connectors: %s', $body['integrationId']),
                [
                    'message' => $message,
                    'channel' => $channel
                ]
            );

            return self::REJECT;
        }

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($channel) {
            $this->cartExpirationProcessor->process($channel);

            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }
}
