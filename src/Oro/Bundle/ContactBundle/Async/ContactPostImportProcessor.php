<?php

namespace Oro\Bundle\ContactBundle\Async;

use Doctrine\DBAL\Exception\RetryableException;
use Oro\Bundle\ContactBundle\Handler\ContactEmailAddressHandler;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Bundle\MessageQueueBundle\Entity\Repository\JobRepository;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Perform actions after contacts import finished.
 * Actualize EmailAddress records - add new emails and remove not existing
 */
class ContactPostImportProcessor implements MessageProcessorInterface
{
    /**
     * @var ContactEmailAddressHandler
     */
    private $contactEmailAddressHandler;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ContactEmailAddressHandler $contactEmailAddressHandler,
        DoctrineHelper $doctrineHelper,
        LoggerInterface $logger
    ) {
        $this->contactEmailAddressHandler = $contactEmailAddressHandler;
        $this->doctrineHelper = $doctrineHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $messageBody = json_decode($message->getBody(), JSON_OBJECT_AS_ARRAY);

        // Skip non import jobs. For example import validate
        if (empty($messageBody['process']) || $messageBody['process'] !== 'import') {
            return self::REJECT;
        }

        // Skip non contact import jobs
        $rootImportJob = $this->getJobRepository()->findJobById((int)$messageBody['rootImportJobId']);
        if ($rootImportJob) {
            $importJobData = explode(':', $rootImportJob->getName());
            if (empty($importJobData[2]) || strpos($importJobData[2], 'oro_contact') === false) {
                return self::REJECT;
            }
        } else {
            return self::REJECT;
        }

        try {
            $this->contactEmailAddressHandler->actualizeContactEmailAssociations();
        } catch (RetryableException $e) {
            $this->logger->error(
                'Deadlock occurred during actualization of contact emails',
                [
                    'exception' => $e
                ]
            );

            return self::REQUEUE;
        }

        return self::ACK;
    }

    private function getJobRepository(): JobRepository
    {
        return $this->doctrineHelper->getEntityRepository(Job::class);
    }
}
