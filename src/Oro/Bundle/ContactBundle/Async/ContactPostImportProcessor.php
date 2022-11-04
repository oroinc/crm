<?php

namespace Oro\Bundle\ContactBundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Oro\Bundle\ContactBundle\Async\Topic\ActualizeContactEmailAssociationsTopic;
use Oro\Bundle\ContactBundle\Handler\ContactEmailAddressHandler;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Perform actions after contacts import finished.
 * Actualize EmailAddress records - add new emails and remove not existing
 */
class ContactPostImportProcessor implements MessageProcessorInterface, TopicSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ContactEmailAddressHandler $contactEmailAddressHandler;

    public function __construct(ContactEmailAddressHandler $contactEmailAddressHandler)
    {
        $this->contactEmailAddressHandler = $contactEmailAddressHandler;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        try {
            $this->contactEmailAddressHandler->actualizeContactEmailAssociations();
        } catch (RetryableException $e) {
            $this->logger->error(
                'Deadlock occurred during actualization of contact emails',
                [
                    'exception' => $e,
                ]
            );

            return self::REQUEUE;
        }

        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [ActualizeContactEmailAssociationsTopic::getName()];
    }
}
