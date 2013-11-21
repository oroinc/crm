<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Connector as ConnectorEntity;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface;

class SyncProcessor implements SyncProcessorInterface
{
    const DEFAULT_BATCH_SIZE         = 15;
    const DEFAULT_EMPTY_RANGES_COUNT = 2; // doesn't affect anything yet

    /** @var EntityManager */
    protected $em;

    /** @var ProcessorRegistry */
    protected $processorRegistry;

    /** @var JobExecutor */
    protected $jobExecutor;

    /** @var TypesRegistry */
    protected $registry;

    /** @var \Closure */
    protected $loggingClosure;

    /**
     * @param EntityManager     $em
     * @param ProcessorRegistry $processorRegistry
     * @param JobExecutor       $jobExecutor
     * @param TypesRegistry     $registry
     */
    public function __construct(
        EntityManager $em,
        ProcessorRegistry $processorRegistry,
        JobExecutor $jobExecutor,
        TypesRegistry $registry
    ) {
        $this->em                = $em;
        $this->processorRegistry = $processorRegistry;
        $this->jobExecutor       = $jobExecutor;
        $this->registry          = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($channelName, $force = false)
    {
        $channel = $this->getChannelByName($channelName);
        /** @var ConnectorEntity[] $connectors */
        $connectors = $channel->getConnectors();

        /** @var ConnectorEntity $connector */
        foreach ($connectors as $connector) {
            try {
                $realConnector = $this->registry->getConnectorTypeBySettingEntity($connector, $channel->getType());
            } catch (\Exception $e) {
                // log and continue
                $this->log($e->getMessage());
                continue;
            }
            if ($force) {
                $mode    = ProcessorRegistry::TYPE_IMPORT;
                $jobName = $realConnector->getImportJobName();
            } else {
                $mode    = ProcessorRegistry::TYPE_IMPORT_VALIDATION;
                $jobName = $realConnector->getImportJobName(true);
            }

            $processorAliases = $this->processorRegistry->getProcessorAliasesByEntity(
                ProcessorRegistry::TYPE_IMPORT,
                $realConnector->getImportEntityFQCN()
            );
            $processorAlias   = reset($processorAliases);

            $configuration = [
                $mode => [
                    'processorAlias' => $processorAlias,
                    'entityName'     => $realConnector->getImportEntityFQCN(),
                    'channelName'    => $channelName,
                    'batchSize'      => self::DEFAULT_BATCH_SIZE,
                    'maxEmptyRanges' => self::DEFAULT_EMPTY_RANGES_COUNT,
                    'connector'      => $connector
                    // @TODO allow to pass logger here
                    //'logger'         => $this->loggingClosure,
                ],
            ];
            $result        = $this->processImport($mode, $jobName, $configuration);
            $this->log($result);
        }
    }

    /**
     * @param string $mode import or validation (dry run, readonly)
     * @param string $jobName
     * @param array  $configuration
     *
     * @return array
     */
    public function processImport($mode, $jobName, $configuration)
    {
        $jobResult = $this->jobExecutor->executeJob($mode, $jobName, $configuration);

        if ($jobResult->isSuccessful()) {
            $message = 'oro_importexport.import.import_success';
        } else {
            $message = 'oro_importexport.import.import_error';
        }

        /** @var ContextInterface $contexts */
        $context = $jobResult->getContext();

        $counts           = [];
        $counts['errors'] = count($jobResult->getFailureExceptions());
        if ($context) {
            $counts['process'] = 0;
            $counts['read']    = $context->getReadCount();
            $counts['process'] += $counts['add'] = $context->getAddCount();
            $counts['process'] += $counts['replace'] = $context->getReplaceCount();
            $counts['process'] += $counts['update'] = $context->getUpdateCount();
            $counts['process'] += $counts['delete'] = $context->getDeleteCount();
            $counts['process'] -= $counts['error_entries'] = $context->getErrorEntriesCount();
            $counts['errors'] += count($context->getErrors());
        }

        $errorsAndExceptions = [];
        if (!empty($counts['errors'])) {
            $errorsAndExceptions = array_slice(
                array_merge(
                    $jobResult->getFailureExceptions(),
                    $context ? $context->getErrors() : []
                ),
                0,
                100
            );
        }

        return [
            'success'      => $jobResult->isSuccessful(),
            'message'      => $message,
            'exceptions'   => $jobResult->getFailureExceptions(),
            'isSuccessful' => $jobResult->isSuccessful() && isset($counts['process']) && $counts['process'] > 0,
            'counts'       => $counts,
            'errors'       => $errorsAndExceptions,
        ];
    }

    /**
     * Get channel entity by it's name
     *
     * @param string $channelName
     *
     * @throws \Exception
     * @return Channel
     */
    protected function getChannelByName($channelName)
    {
        /** @var $item Channel */
        $channel = $this->em
            ->getRepository('OroIntegrationBundle:Channel')
            ->findOneBy(['name' => $channelName]);

        if (!$channel) {
            throw new \Exception(sprintf('Channel \'%s\' not found', $channelName));
        }

        return $channel;
    }

    /**
     * @param callable $closure
     *
     * @return $this
     */
    public function setLogClosure(\Closure $closure)
    {
        $this->loggingClosure = $closure;

        return $this;
    }

    /**
     * @return callable
     */
    public function log()
    {
        $context = func_get_args();

        if (is_callable($this->loggingClosure)) {
            $closure = $this->loggingClosure;
            $closure($context);
        }
    }
}
