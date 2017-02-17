<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Component\Log\OutputLogger;

use OroCRM\Bundle\MagentoBundle\Manager\CustomerAddressManager;

class CopyCustomerAddressesToContactCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const BATCH_SIZE = 25;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('oro:magento:copy-data-to-contact:addresses')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'If option exists then customer addresses will be copied to contact for given magento customer by id'
            )
            ->addOption(
                'integration-id',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'If option exists then customer addresses will be copied to contact 
                for given magento customers by integration_id'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of customers in batch. The default value is 25.'
            )
            ->setDescription('Make copy addresses of magento customers to the contact');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);
        $logger->info('Executing command started.');

        $integrationIds = $input->getOption('integration-id');
        $ids = $input->getOption('id');
        $batchSize = $input->getOption('batch-size') ? $input->getOption('batch-size') : self::BATCH_SIZE;

        $logger->info('Parameters:');
        if ($integrationIds) {
            foreach ($integrationIds as $item) {
                $logger->info(sprintf('--integration-id=%s', $item));
            }
        }
        if ($ids) {
            foreach ($integrationIds as $item) {
                $logger->info(sprintf('--id=%s', $item));
            }
        }
        if ($batchSize) {
            $logger->info(sprintf('--batch-size=%s', $batchSize));
            $logger->info('');
        }

        /** @var CustomerAddressManager $customerAddressManager */
        $customerAddressManager = $this->container->get('oro_magento.manager.customer_address_manager');
        $customerAddressManager->setLogger($logger);
        $customerAddressManager->copyToContact($ids, $integrationIds, $batchSize);

        $logger->info('Executing command finished.');
    }
}
