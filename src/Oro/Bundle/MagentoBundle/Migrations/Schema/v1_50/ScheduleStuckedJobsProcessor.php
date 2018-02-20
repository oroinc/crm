<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50;

use Oro\Bundle\EntityBundle\ORM\NativeQueryExecutorHelper;
use Oro\Bundle\WorkflowBundle\Async\Topics;
use Oro\Bundle\WorkflowBundle\Entity\ProcessJob;
use Oro\Bundle\WorkflowBundle\Model\ProcessLogger;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class ScheduleStuckedJobsProcessor implements MessageProcessorInterface
{
    const TOPIC_NAME = 'oro_magento.schedule_stucked_jobs';

    /** @var MessageProducerInterface */
    protected $messageProducer;

    /** @var NativeQueryExecutorHelper */
    protected $queryHelper;

    /** @var array List of triggers to schedule */
    protected $processTriggers = [
        'magento_customer_creation',
        'magento_customer_export'
    ];

    /**
     * @param MessageProducerInterface $messageProducer
     * @param NativeQueryExecutorHelper $queryHelper
     * @param ProcessLogger $processLogger
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        NativeQueryExecutorHelper $queryHelper,
        ProcessLogger $processLogger
    ) {
        $this->messageProducer = $messageProducer;
        $this->queryHelper = $queryHelper;
        $this->logger = $processLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $em = $this->queryHelper->getManager(ProcessJob::class);
        $queryBuilder = $em->getRepository(ProcessJob::class)->createQueryBuilder('job');
        $processJobs = $queryBuilder
            ->leftJoin('job.processTrigger', 'trigger')
            ->where($queryBuilder->expr()->in('trigger.definition', ':triggerDefinitionNames'))
            ->setParameter('triggerDefinitionNames', $this->processTriggers)
            ->getQuery()
            ->getResult();

        foreach ($processJobs as $processJob) {
            $message = new Message();
            $message->setBody(['process_job_id' => $processJob->getId()]);

            $this->messageProducer->send(Topics::EXECUTE_PROCESS_JOB, $message);
            $this->logger->debug('Process queued', $processJob->getProcessTrigger(), $processJob->getData());
        }

        return self::ACK;
    }
}
