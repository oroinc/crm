<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Connector as ConnectorEntity;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class SyncProcessor implements SyncProcessorInterface
{
    const JOB_VALIDATE_IMPORT = 'mage_customer_import_validation';
    const JOB_IMPORT          = 'mage_customer_import';
    const ENTITY_NAME         = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';

    /** @var EntityManager */
    protected $em;

    /** @var ProcessorRegistry */
    protected $processorRegistry;

    /** @var JobExecutor */
    protected $jobExecutor;

    /**
     * @param EntityManager $em
     * @param ProcessorRegistry $processorRegistry
     * @param JobExecutor $jobExecutor
     */
    public function __construct(
        EntityManager $em,
        ProcessorRegistry $processorRegistry,
        JobExecutor $jobExecutor
    ) {
        $this->em = $em;
        $this->processorRegistry = $processorRegistry;
        $this->jobExecutor = $jobExecutor;
    }

    /**
     * @param string $channelName
     * @throws \Exception
     */
    protected function init($channelName)
    {
        /*
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $settings = [
            'last_sync_date' => $now->sub(\DateInterval::createFromDateString('1 month')),
            'sync_range'     => '1 week',
            'api_user'       => 'api_user',
            'api_key'        => 'api_user',
            'wsdl_url'       => 'http://mage.dev.lxc/index.php/api/v2_soap/?wsdl=1',
        ];
        */
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

        }


        // get processor
        $processorAliases = $this->processorRegistry->getProcessorAliasesByEntity(
            ProcessorRegistry::TYPE_IMPORT,
            self::ENTITY_NAME
        );
        $processorAlias = reset($processorAliases);

        if ($force) {
            $mode = ProcessorRegistry::TYPE_IMPORT;
            $jobName = self::JOB_IMPORT;
        } else {
            $mode = ProcessorRegistry::TYPE_IMPORT_VALIDATION;
            $jobName = self::JOB_VALIDATE_IMPORT;
        }

        $configuration = [
            $mode => [
                'processorAlias' => $processorAlias,
                'entityName'     => self::ENTITY_NAME,
                'channelName'    => $channelName,
                'connector'      => $this->connector,
            ],
        ];

        if ($force) {
            $result = $this->processImport($mode, $jobName, self::$configuration);
        } else {
            $result = $this->processValidation($configuration);
        }

        var_dump($result);
    }

    /**
     * @param string $mode import or validation (dry run, readonly)
     * @param string $jobName
     * @param array $configuration
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

        $counts = [];
        $counts['errors'] = count($jobResult->getFailureExceptions());
        if ($context) {
            $counts['process'] = 0;
            $counts['read'] = $context->getReadCount();
            $counts['process'] += $counts['add']     = $context->getAddCount();
            $counts['process'] += $counts['replace'] = $context->getReplaceCount();
            $counts['process'] += $counts['update']  = $context->getUpdateCount();
            $counts['process'] += $counts['delete']  = $context->getDeleteCount();
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
            'success' => $jobResult->isSuccessful(),
            'message' => $message,
            'exceptions' => $jobResult->getFailureExceptions(),
            'isSuccessful' => $jobResult->isSuccessful() && isset($counts['process']) && $counts['process'] > 0,
            'counts' => $counts,
            'errors' => $errorsAndExceptions,
        ];
    }

    /**
     * Get channel entity by it's name
     *
     * @param string $channelName
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
}
