<?php

namespace Oro\Bundle\MagentoBundle\Command;

use Oro\Bundle\MagentoBundle\Manager\CustomerAddressManager;
use Oro\Component\Log\OutputLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make copy addresses of magento customers to the contact.
 */
class CopyCustomerAddressesToContactCommand extends Command
{
    const BATCH_SIZE = 25;

    /** @var string */
    protected static $defaultName = 'oro:magento:copy-data-to-contact:addresses';

    /** @var CustomerAddressManager */
    private $customerAddressManager;

    /**
     * @param CustomerAddressManager $customerAddressManager
     */
    public function __construct(CustomerAddressManager $customerAddressManager)
    {
        parent::__construct();

        $this->customerAddressManager = $customerAddressManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->addOption(
                'id',
                null,
                InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY,
                'If option exists then customer addresses will be copied to contact for given magento customer by id'
            )
            ->addOption(
                'integration-id',
                null,
                InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY,
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
        $logger    = new OutputLogger($output);
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

        $this->customerAddressManager->setLogger($logger);
        $this->customerAddressManager->copyToContact($ids, $integrationIds, $batchSize);
        $logger->info('Executing command finished.');
    }
}
