<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Doctrine\ORM\EntityManager;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\Log\OutputLogger;

use OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class InitialSyncCommand extends ContainerAwareCommand
{
    const COMMAND_NAME = 'oro:magento:initial:sync';

    const SYNC_PROCESSOR = 'orocrm_magento.provider.initial_sync_processor';

    const STATUS_SUCCESS = 0;
    const STATUS_FAILED = 255;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->addOption(
                'integration-id',
                'i',
                InputOption::VALUE_REQUIRED,
                'Sync will be performed for given integration id'
            )
            ->addOption(
                'skip-dictionary',
                null,
                InputOption::VALUE_NONE,
                'Skip dictionaries synchronization'
            )
            ->addOption(
                'connector',
                'con',
                InputOption::VALUE_OPTIONAL,
                'If option exists sync will be performed for given connector name'
            )
            ->setDescription('Run initial synchronization for magento channel.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // disable search listener on-fly processing
        $searchListener = $this->getContainer()->get('oro_search.index_listener');
        $searchListener->setRealTimeUpdate(false);

        $skipDictionary = (bool)$input->getOption('skip-dictionary');
        $integrationId = $input->getOption('integration-id');
        $logger = $this->getLogger($output);
        $this->getContainer()->get('oro_integration.logger.strategy')->setLogger($logger);
        $this->initEntityManager();

        if ($this->isJobRunning($integrationId)) {
            $logger->warning('Job already running. Terminating....');

            return self::STATUS_SUCCESS;
        }

        $integration = $this->getIntegrationChannelRepository()->getOrLoadById($integrationId);
        if (!$integration) {
            $logger->critical(sprintf('Integration with given ID "%d" not found', $integrationId));

            return self::STATUS_FAILED;
        } elseif (!$integration->isEnabled()) {
            $logger->warning('Integration is disabled. Terminating....');

            return self::STATUS_SUCCESS;
        }

        $this->scheduleAnalyticRecalculation($integration);

        $processor = $this->getSyncProcessor($logger);
        try {
            $logger->notice(sprintf('Run initial sync for "%s" integration.', $integration->getName()));

            $connector = $input->getOption('connector');
            $result = $processor->process($integration, $connector, ['skip-dictionary' => $skipDictionary]);
            $exitCode = $result ?: self::STATUS_FAILED;
        } catch (\Exception $e) {
            $logger->critical($e->getMessage(), ['exception' => $e]);
            $exitCode = self::STATUS_FAILED;
        }

        // Restore search listener configuration
        $searchListener->setRealTimeUpdate($this->getContainer()->getParameter('oro_search.realtime_update'));

        $logger->notice('Completed');

        return $exitCode;
    }

    /**
     * @param OutputInterface $output
     * @return OutputLogger
     */
    protected function getLogger(OutputInterface $output)
    {
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        return new OutputLogger($output);
    }

    /**
     * Check is job running (from previous schedule)
     *
     * @param null|int $integrationId
     *
     * @return bool
     */
    protected function isJobRunning($integrationId)
    {
        $running = $this->getIntegrationChannelRepository()
            ->getRunningSyncJobsCount($this->getName(), $integrationId);

        return $running > 1;
    }

    protected function initEntityManager()
    {
        $this->getEntityManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine')->getManager();
    }

    /**
     * @param LoggerInterface $logger
     * @return InitialSyncProcessor
     */
    protected function getSyncProcessor($logger)
    {
        $processor = $this->getService(self::SYNC_PROCESSOR);
        $processor->getLoggerStrategy()->setLogger($logger);

        return $processor;
    }

    /**
     * @return ChannelRepository
     */
    protected function getIntegrationChannelRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository('OroIntegrationBundle:Channel');
    }

    /**
     * Get service from DI container by id
     *
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }

    /**
     * @param Integration $integration
     */
    protected function scheduleAnalyticRecalculation(Integration $integration)
    {
        $dataChannel = $this->getDataChannelByChannel($integration);
        /** @var RFMMetricStateManager $rfmStateManager */
        $rfmStateManager = $this->getService('orocrm_analytics.model.rfm_state_manager');
        $rfmStateManager->scheduleRecalculation($dataChannel);
    }

    /**
     * @param Integration $integration
     * @return Channel
     */
    protected function getDataChannelByChannel(Integration $integration)
    {
        return $this->getContainer()->get('doctrine')
            ->getRepository('OroCRMChannelBundle:Channel')
            ->findOneBy(['dataSource' => $integration]);
    }
}
