<?php

namespace OroCRM\Bundle\ChannelBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;

class LifetimeAverageAggregateCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const COMMAND_NAME = 'oro:cron:lifetime-average:aggregate';

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 4 * * *';
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Run daily aggregation of average lifetime value per channel');
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'This option enforces regeneration of aggregation values from scratch(Useful after system timezone change)'
        );
        $this->addOption(
            'use-delete',
            null,
            InputOption::VALUE_NONE,
            'This option enforces to use DELETE statement instead TRUNCATE for force mode'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var LifetimeValueAverageAggregationRepository $repo */
        $repo  = $this->getService('doctrine')->getRepository('OroCRMChannelBundle:LifetimeValueAverageAggregation');
        $force = $input->getOption('force');
        if ($force) {
            $output->writeln('<comment>Removing existing data...</comment>');
            $repo->clearTableData($input->getOption('use-delete'));
        }

        $localeSettings = $this->getService('oro_locale.settings');
        $repo->aggregate($localeSettings->getTimeZone(), $force);

        $output->writeln('<info>Completed!</info>');
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }
}
