<?php

namespace Oro\Bundle\MagentoBundle\Command;

use Oro\Bundle\MagentoBundle\Manager\CustomerContactManager;
use Oro\Component\Log\OutputLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates contacts for magento customers.
 */
class AddContactsToMagentoCustomersCommand extends Command
{
    const BATCH_SIZE = 25;

    /** @var string */
    protected static $defaultName = 'oro:magento:customer:add-contacts';

    /** @var CustomerContactManager */
    private $customerContactManager;

    /**
     * @param CustomerContactManager $customerContactManager
     */
    public function __construct(CustomerContactManager $customerContactManager)
    {
        parent::__construct();

        $this->customerContactManager = $customerContactManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->addOption(
                'integration-id',
                null,
                InputOption::VALUE_OPTIONAL|InputOption::VALUE_IS_ARRAY,
                'If option exists then contacts will be added for given magento
                 customers by integration_id'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of customers in batch. The default value is 25.'
            )
            ->setDescription('Create contacts for magento customers');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger    = new OutputLogger($output);
        $logger->info('Executing command started.');

        $integrationIds = $input->getOption('integration-id');
        $batchSize = $input->getOption('batch-size') ? $input->getOption('batch-size') : self::BATCH_SIZE;

        $logger->info('Parameters:');
        if ($integrationIds) {
            foreach ($integrationIds as $item) {
                $logger->info(sprintf('--integration-id=%s', $item));
            }
        }

        if ($batchSize) {
            $logger->info(sprintf('--batch-size=%s', $batchSize));
            $logger->info('');
        }

        $this->customerContactManager->setLogger($logger);
        $this->customerContactManager->fillContacts($integrationIds, $batchSize);
        $logger->info('Executing command finished.');
    }
}
