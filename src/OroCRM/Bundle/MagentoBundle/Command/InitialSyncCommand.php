<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Doctrine\ORM\EntityManager;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\Log\OutputLogger;
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
            ->setName(static::COMMAND_NAME)
            ->addOption(
                'integration-id',
                'i',
                InputOption::VALUE_REQUIRED,
                'Sync will be performed for given integration id'
            )
            ->setDescription('Runs synchronization for magento channels to process expiration of merged carts');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $integrationId = $input->getOption('integration-id');
        $logger = $this->getLogger($output);
        $this->initEntityManager();

        if ($this->isJobRunning($integrationId)) {
            $logger->warning('Job already running. Terminating....');

            return self::STATUS_SUCCESS;
        }

        $integration = $this->getIntegrationChannelRepository()->getOrLoadById($integrationId);
        if (!$integration) {
            $logger->critical(sprintf('Integration with given ID "%d" not found', $integrationId));

            return self::STATUS_FAILED;
        }

        $processor = $this->getSyncProcessor($logger);
        try {
            $logger->notice(sprintf('Run initial sync for "%s" integration.', $integration->getName()));

            $result = $processor->process($integration);
            $exitCode = $result ?: self::STATUS_FAILED;
        } catch (\Exception $e) {
            $logger->critical($e->getMessage(), ['exception' => $e]);
            $exitCode = self::STATUS_FAILED;
        }

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
        /** @var EntityManager $entityManager */
        $entityManager = $this->getService('doctrine')->getManager();
        $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
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
}
