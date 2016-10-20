<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\SecurityBundle\Authentication\Token\ConsoleToken;

class JobExecutionSubscriber implements EventSubscriberInterface
{
    const CHANNEL_CONFIG = 'channel';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param TokenStorageInterface $tokenStorage
     */
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
            EventInterface::BEFORE_JOB_EXECUTION => 'beforeJobExecution',
        ];
    }

    /**
     * @param JobExecutionEvent $event
     */
    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $config = $event->getJobExecution()->getJobInstance()->getRawConfiguration();
        if (!isset($config[static::CHANNEL_CONFIG])) {
            return;
        }
        $channelId = $config[static::CHANNEL_CONFIG];

        $integration = $this->getIntegration($channelId);
        if ($integration) {
            $this->updateToken($integration);
        }
    }


    /**
     * @param int|null $channelId
     *
     * @return Integration
     */
    protected function getIntegration($channelId = null)
    {
        if (!$channelId) {
            return;
        }

        return $this->doctrineHelper->getEntityRepositoryForClass('OroIntegrationBundle:Channel')
            ->find($channelId);
    }

    /**
     * @param Integration $integration
     */
    protected function updateToken(Integration $integration)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            $token = new ConsoleToken();
            $this->tokenStorage->setToken($token);
        }

        $token->setOrganizationContext($integration->getOrganization());
    }
}
