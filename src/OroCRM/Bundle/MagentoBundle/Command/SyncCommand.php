<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class SyncCommand
 * Console command implementation
 *
 * @package Oro\Bundle\MagentoBundle\Command
 */
class SyncCommand extends ContainerAwareCommand
{
    const CUSTOMER_SYNC_PROCESSOR = 'orocrm_magento.customer_sync.processor';

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('orocrm:magento:sync');
        $this->setDescription('Sync magento entities (currently only import customers)');
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        $someData = [];

        $processor = $this->getContainer()->get(self::CUSTOMER_SYNC_PROCESSOR);
        $processor->process($someData);

        $output->writeln('Completed');
    }
}
