<?php

namespace Oro\Bundle\AnalyticsBundle\Command;

use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class CalculateAnalyticsCommand extends Command implements ContainerAwareInterface, CronCommandInterface
{
    use ContainerAwareTrait;

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

            $this->getCalculateAnalyticsScheduler()->scheduleForChannel($channelId, $customerIds);
        } else {
            $output->writeln('Schedule analytics calculation for all channels.');

            $this->getCalculateAnalyticsScheduler()->scheduleForAllChannels();
        }

        $output->writeln('Completed');
    }

    /**
     * @return CalculateAnalyticsScheduler
     */
    private function getCalculateAnalyticsScheduler()
    {
        return $this->container->get('oro_analytics.schedule_calculate_analytics');
    }
}
