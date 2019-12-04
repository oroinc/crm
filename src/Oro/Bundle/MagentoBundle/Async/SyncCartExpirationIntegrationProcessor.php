<?php
namespace Oro\Bundle\MagentoBundle\Async;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SyncCartExpirationIntegrationProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    use IntegrationTokenAwareTrait;

    /**
     * @var CartExpirationProcessor
     */
    private $cartExpirationProcessor;

    /**
     * @var ManagerRegistry
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
     * @param ManagerRegistry $doctrine
     * @param CartExpirationProcessor $cartExpirationProcessor
     * @param JobRunner $jobRunner
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManagerRegistry $doctrine,
        CartExpirationProcessor $cartExpirationProcessor,
        JobRunner $jobRunner,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger
    ) {
        $this->doctrine = $doctrine;
        $this->cartExpirationProcessor = $cartExpirationProcessor;
        $this->jobRunner = $jobRunner;
        $this->tokenStorage = $tokenStorage;
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
            $this->logger->critical('The message invalid. It must have integrationId set');

            return self::REJECT;
        }

        $ownerId = $message->getMessageId();
        $jobName = 'oro_magento:sync_cart_expiration_integration:'.$body['integrationId'];

        /** @var IntegrationRepository $repository */
        $repository = $this->doctrine->getRepository(Integration::class);
        $integration = $repository->getOrLoadById($body['integrationId']);

        if (! $integration || ! $integration->isEnabled()) {
            $this->logger->error(
                sprintf('The integration should exist and be enabled: %s', $body['integrationId'])
            );

            return self::REJECT;
        }

        if (! is_array($integration->getConnectors()) || ! in_array('cart', $integration->getConnectors())) {
            $this->logger->error(
                sprintf('The integration should have cart in connectors: %s', $body['integrationId']),
                ['integration' => $integration]
            );

            return self::REJECT;
        }

        try {
            $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($integration) {
                $this->setTemporaryIntegrationToken($integration);
                $this->cartExpirationProcessor->process($integration);
                return true;
            });
        } catch (ExtensionRequiredException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);

            return self::REJECT;
        }

        return $result ? self::ACK : self::REJECT;
    }
}
