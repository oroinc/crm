<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Authentication\Token\ConsoleToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Sets the organization from the job configuration channel to the security context.
 */
class JobExecutionSubscriber implements EventSubscriberInterface
{
    private const CHANNEL_CONFIG = 'channel';

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(DoctrineHelper $doctrineHelper, TokenStorageInterface $tokenStorage)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BEFORE_JOB_EXECUTION => 'beforeJobExecution'
        ];
    }

    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $config = $event->getJobExecution()->getJobInstance()->getRawConfiguration();
        if (!isset($config[self::CHANNEL_CONFIG])) {
            return;
        }

        $channelId = $config[self::CHANNEL_CONFIG];
        if (!$channelId) {
            return;
        }

        $channel = $this->doctrineHelper->getEntityRepositoryForClass(Channel::class)->find($channelId);
        if ($channel) {
            $this->updateToken($channel);
        }
    }

    private function updateToken(Channel $channel)
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            $token = new ConsoleToken();
            $this->tokenStorage->setToken($token);
        }

        $token->setOrganization($channel->getOrganization());
    }
}
