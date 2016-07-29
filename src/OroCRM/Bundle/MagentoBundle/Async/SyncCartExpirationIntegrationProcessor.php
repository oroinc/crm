<?php
namespace OroCRM\Bundle\MagentoBundle\Async;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use OroCRM\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
     * @param RegistryInterface $doctrine
     * @param CartExpirationProcessor $cartExpirationProcessor
     */
    public function __construct(RegistryInterface $doctrine, CartExpirationProcessor $cartExpirationProcessor)
    {
        $this->doctrine = $doctrine;
        $this->cartExpirationProcessor = $cartExpirationProcessor;
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
        // TODO CRM-5839 unique job

        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integrationId' => null,
        ], $body);

        if (false == $body['integrationId']) {
            throw new \LogicException('The message invalid. It must have integrationId set');
        }

        /** @var ChannelRepository $repository */
        $repository = $this->doctrine->getRepository(Channel::class);
        $channel = $repository->getOrLoadById($body['integrationId']);
        if (!$channel) {
            return self::REJECT;
        }
        if (!$channel->isEnabled()) {
            return self::REJECT;
        }
        $connectors = $channel->getConnectors() ?: [];
        if (!in_array('cart', $connectors)) {
            return self::REJECT;
        }

        $this->cartExpirationProcessor->process($channel);

        return self::ACK;
    }
}
