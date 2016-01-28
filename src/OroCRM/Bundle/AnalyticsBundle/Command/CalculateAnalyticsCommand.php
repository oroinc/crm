<?php

namespace OroCRM\Bundle\AnalyticsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\AnalyticsBundle\Model\StateManager;

class CalculateAnalyticsCommand extends ContainerAwareCommand implements CronCommandInterface
{
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
            ->setName(self::COMMAND_NAME)
            ->setDescription('Calculate all registered analytic metrics')
            ->addOption('channel', null, InputOption::VALUE_OPTIONAL, 'Data Channel id to process');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');

        $channel = $input->getOption('channel');

        if ($this->getStateManager()->isJobRunning($channel ? sprintf('--channel=%s', $channel) : null)) {
            $output->writeln('<error>Job already running. Terminating....</error>');

            return;
        }

        $channels = $this->getChannels($channel);
        foreach ($channels as $channel) {
            $output->writeln($formatter->formatSection('Process', sprintf('Channel: %s', $channel->getName())));

            $this->getAnalyticBuilder()->build($channel);

            $output->writeln($formatter->formatSection('Done', sprintf('Channel: %s updated', $channel->getName())));
        }
    }

    /**
     * @param int $channelId
     *
     * @return BufferedQueryResultIterator|Channel[]
     */
    protected function getChannels($channelId = null)
    {
        $className = $this->getContainer()->getParameter('orocrm_channel.entity.class');
        $qb        = $this->getDoctrineHelper()->getEntityRepository($className)
            ->createQueryBuilder('c');

        $qb->orderBy('c.id');
        $qb->andWhere('c.status = :status');
        $qb->setParameter('status', Channel::STATUS_ACTIVE);

        if ($channelId) {
            $qb->andWhere('c.id = :id');
            $qb->setParameter('id', $channelId);
        }

        $analyticsInterface = $this->getContainer()->getParameter('orocrm_analytics.model.analytics_aware_interface');

        return new \CallbackFilterIterator(
            new BufferedQueryResultIterator($qb),
            function (Channel $channel) use ($analyticsInterface) {
                $identityFQCN = $channel->getCustomerIdentity();

                return is_a($identityFQCN, $analyticsInterface, true);
            }
        );
    }

    /**
     * @return AnalyticsBuilder
     */
    protected function getAnalyticBuilder()
    {
        return $this->getContainer()->get('orocrm_analytics.builder');
    }

    /**
     * @return DoctrineHelper
     */
    protected function getDoctrineHelper()
    {
        return $this->getContainer()->get('oro_entity.doctrine_helper');
    }

    /**
     * @return StateManager
     */
    protected function getStateManager()
    {
        return $this->getContainer()->get('orocrm_analytics.model.state_manager');
    }
}
