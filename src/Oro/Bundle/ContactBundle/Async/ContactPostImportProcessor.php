<?php

namespace Oro\Bundle\ContactBundle\Async;

use Oro\Bundle\ContactBundle\Handler\ContactEmailAddressHandler;
use Oro\Bundle\EntityBundle\ORM\DatabaseExceptionHelper;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobStorage;
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
     * @var DatabaseExceptionHelper
     */
    private $databaseExceptionHelper;

    /**
     * @var JobStorage
     */
    private $jobStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ContactEmailAddressHandler $contactEmailAddressHandler
     * @param DatabaseExceptionHelper $databaseExceptionHelper
     * @param JobStorage $jobStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContactEmailAddressHandler $contactEmailAddressHandler,
        DatabaseExceptionHelper $databaseExceptionHelper,
        JobStorage $jobStorage,
        LoggerInterface $logger
    ) {
        $this->contactEmailAddressHandler = $contactEmailAddressHandler;
        $this->databaseExceptionHelper = $databaseExceptionHelper;
        $this->jobStorage = $jobStorage;
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
        $rootImportJob = $this->jobStorage->findJobById($messageBody['rootImportJobId']);
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
        } catch (\Exception $e) {
            $driverException = $this->databaseExceptionHelper->getDriverException($e);

            if ($driverException && $this->databaseExceptionHelper->isDeadlock($driverException)) {
                $this->logger->error(
                    'Deadlock occurred during actualization of contact emails',
                    [
                        'exception' => $e
                    ]
                );

                return self::REQUEUE;
            }

            throw $e;
        }

        return self::ACK;
    }
}
