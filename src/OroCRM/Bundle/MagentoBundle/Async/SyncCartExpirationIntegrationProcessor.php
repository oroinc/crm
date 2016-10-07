<?php
namespace OroCRM\Bundle\MagentoBundle\Async;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
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
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @param RegistryInterface $doctrine
     * @param CartExpirationProcessor $cartExpirationProcessor
     * @param JobRunner $jobRunner
     */
    public function __construct(
        RegistryInterface $doctrine,
        CartExpirationProcessor $cartExpirationProcessor,
        JobRunner $jobRunner
    ) {
        $this->doctrine = $doctrine;
        $this->cartExpirationProcessor = $cartExpirationProcessor;
        $this->jobRunner = $jobRunner;
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
        // TODO CRM-5838 message could be redelivered on dbal transport if run for a long time.

        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integrationId' => null,
        ], $body);

        if (! $body['integrationId']) {
            throw new \LogicException('The message invalid. It must have integrationId set');
        }

        $ownerId = $message->getMessageId();
        $jobName = 'orocrm_magento:sync_cart_expiration_integration:'.$body['integrationId'];

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($body) {
            /** @var ChannelRepository $repository */
            $repository = $this->doctrine->getRepository(Channel::class);
            $channel = $repository->getOrLoadById($body['integrationId']);

            if (! $channel ||
                ! $channel->isEnabled() ||
                ! is_array($channel->getConnectors()) ||
                ! in_array('cart', $channel->getConnectors())
            ) {
                return false;
            }

            $this->cartExpirationProcessor->process($channel);

            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }
}
