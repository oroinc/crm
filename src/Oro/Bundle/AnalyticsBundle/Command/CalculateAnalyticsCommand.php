<?php

namespace Oro\Bundle\AnalyticsBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The CLI command to calculate all registered analytic metrics.
 */
class CalculateAnalyticsCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:analytic:calculate';

    /** @var ManagerRegistry */
    private $registry;

    /** @var CalculateAnalyticsScheduler */
    private $calculateAnalyticsScheduler;

    /**
     * @param ManagerRegistry $registry
     * @param CalculateAnalyticsScheduler $calculateAnalyticsScheduler
     */
    public function __construct(ManagerRegistry $registry, CalculateAnalyticsScheduler $calculateAnalyticsScheduler)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->calculateAnalyticsScheduler = $calculateAnalyticsScheduler;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 0 * * *';
    }

    /**
     * @deprecated Since 2.0.3. Will be removed in 2.1. Must be refactored at BAP-13973
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Calculate all registered analytic metrics')
            ->addOption('channel', null, InputOption::VALUE_OPTIONAL, 'Data Channel id to process')
            ->addOption(
                'ids',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Customer identity ids for given channel'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channelId = $input->getOption('channel');
        $customerIds = $input->getOption('ids');

        if (!$channelId && $customerIds) {
            throw new \InvalidArgumentException('Option "ids" does not work without "channel"');
        }

        if ($channelId) {
            $output->writeln(sprintf('Schedule analytics calculation for "%s" channel.', $channelId));

            $channel = $this->registry->getRepository(Channel::class)->find($channelId);

            // check if given channel is active.
            if (Channel::STATUS_ACTIVE != $channel->getStatus()) {
                $output->writeln(sprintf('Channel not active: %s', $channelId));

                return;
            }

            // check if the channel's customer supports analytics.
            if (false === is_a($channel->getCustomerIdentity(), AnalyticsAwareInterface::class, true)) {
                $output->writeln(
                    sprintf('Channel is not supposed to calculate analytics: %s', $channelId)
                );

                return;
            }

            $this->calculateAnalyticsScheduler->scheduleForChannel($channelId, $customerIds);
        } else {
            $output->writeln('Schedule analytics calculation for all channels.');

            $this->calculateAnalyticsScheduler->scheduleForAllChannels();
        }

        $output->writeln('Completed');
    }
}
