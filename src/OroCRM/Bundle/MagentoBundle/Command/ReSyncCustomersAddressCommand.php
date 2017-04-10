<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Component\Log\OutputLogger;

use OroCRM\Bundle\MagentoBundle\Manager\CustomerInfoManager;

class ReSyncCustomersAddressCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const BATCH_SIZE = 25;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('oro:magento:re-sync-customer-address')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY,
                'If option exists then customer addresses will be resynced for given magento customer by id'
            )
            ->addOption(
                'integration-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Customer addresses will be resynced 
                for given magento customers by integration_id'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of customers in batch. The default value is 25.'
            )
            ->setDescription('Make resync addresses of magento customers');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger    = new OutputLogger($output);
        $integrationId = $input->getOption('integration-id');
        if (!$integrationId) {
            throw new \RuntimeException(
                'The "--integration-id" option is required.'
            );
        }
        $ids = $input->getOption('id');
        $batchSize = $input->getOption('batch-size') ? $input->getOption('batch-size') : self::BATCH_SIZE;

        $logger->info('Executing command started.');
        $logger->info('Parameters:');
        $logger->info(sprintf('--integration-id=%s', $integrationId));

        if ($ids) {
            foreach ($ids as $item) {
                $logger->info(sprintf('--id=%s', $item));
            }
        }

        if ($batchSize) {
            $logger->info(sprintf('--batch-size=%s', $batchSize));
            $logger->info('');
        }

        /** @var CustomerInfoManager $customerInfoManager */
        $customerInfoManager = $this->container->get('oro_magento.manager.customer_info_manager');
        $customerInfoManager->setLogger($logger);
        $customerInfoManager->reSyncData($integrationId, $ids, $batchSize);
        $logger->info('Executing command finished.');
    }
}
