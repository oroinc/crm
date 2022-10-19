<?php
declare(strict_types=1);

namespace Oro\Bundle\ChannelBundle\Command;

use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Schedules daily aggregation of average lifetime value per sales channel.
 */
class LifetimeAverageAggregateCommand extends Command implements CronCommandScheduleDefinitionInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:lifetime-average:aggregate';

    private MessageProducerInterface $messageProducer;

    public function __construct(MessageProducerInterface $messageProducer)
    {
        parent::__construct();

        $this->messageProducer = $messageProducer;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDefinition(): string
    {
        return '0 4 * * *';
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function configure()
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Regenerate aggregated data from scratch')
            ->addOption('use-delete', null, InputOption::VALUE_NONE, 'Use DELETE instead of TRUNCATE in SQL')
            ->setDescription('Schedules daily aggregation of average lifetime value per sales channel.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command schedules daily aggregation of average lifetime value per sales channel.

This command only schedules the job by adding a message to the message queue, so ensure
that the message consumer processes (<info>oro:message-queue:consume</info>) are running
for the actual data aggregation to happen.

  <info>php %command.full_name%</info>

The <info>--force</info> option can be used to regenerate data from scratch (may be useful after the system
timezone change):

  <info>php %command.full_name% --force</info>

The <info>--use-delete</info> option enables DELETE statements in SQL instead of TRUNCATE, which may help to regenerate
the data from scratch faster on some large data sets:

  <info>php %command.full_name% --force --use-delete</info>

HELP
            )
            ->addUsage('--force')
            ->addUsage('--force --use-delete')
        ;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->messageProducer->send(
            AggregateLifetimeAverageTopic::getName(),
            [
                'force' => (bool) $input->getOption('force'),
                'use_truncate' => ! (bool) $input->getOption('use-delete'),
            ]
        );

        $output->writeln('<info>Completed!</info>');

        return 0;
    }
}
