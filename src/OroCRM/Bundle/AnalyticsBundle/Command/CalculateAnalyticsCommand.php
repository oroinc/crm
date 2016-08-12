<?php

namespace OroCRM\Bundle\AnalyticsBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use OroCRM\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CalculateAnalyticsCommand extends Command implements ContainerAwareInterface, CronCommandInterface
{
    use ContainerAwareTrait;

    // TODO CRM-5838  remove these constants
    const BATCH_SIZE   = 200;
    const COMMAND_NAME = 'oro:cron:analytic:calculate';

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 0 * * *';
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:analytic:calculate')
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

            $this->getScheduleCalculateAnalyticsService()->scheduleForChannel($channelId, $customerIds);
        } else {
            $output->writeln('Schedule analytics calculation for all channels.');

            $this->getScheduleCalculateAnalyticsService()->scheduleForAllChannels(false);
        }

        $output->writeln('Completed');
    }

    /**
     * @return ScheduleCalculateAnalyticsService
     */
    private function getScheduleCalculateAnalyticsService()
    {
        return $this->container->get('orocrm_analytics.schedule_calculate_analytics');
    }
}
