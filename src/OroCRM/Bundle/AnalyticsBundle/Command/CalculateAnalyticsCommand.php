<?php

namespace OroCRM\Bundle\AnalyticsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity;

class CalculateAnalyticsCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const FLUSH_BATCH_SIZE = 25;

    const COMMAND_NAME = 'oro:cron:analytic:calculate';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var AnalyticsBuilder
     */
    protected $analyticsBuilder;

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
            ->addOption(
                'ids',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Customer identity ids for given channel'
            )
            ->addOption(
                'channel',
                null,
                InputOption::VALUE_OPTIONAL,
                'Data Channel id to process'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $input->getOption('channel');
        $ids = $input->getOption('ids');

        if (!$channel && $ids) {
            $output->writeln('<error>Option "ids" does not work without "channel"</error>');

            return;
        }

        $channels = $this->getChannels($channel);

        foreach ($channels as $channel) {
            $customerIdentityClass = $channel->getCustomerIdentity();
            $analyticsInterface = $this->getAnalyticsAwareInterface();
            if (!in_array($analyticsInterface, class_implements($customerIdentityClass))) {
                $output->writeln(
                    [
                        sprintf(
                            '<error>Channel #%s: %s skipped.</error>',
                            $channel->getId(),
                            $channel->getName()
                        ),
                        sprintf('    %s does not implements %s.', $customerIdentityClass, $analyticsInterface)
                    ]
                );

                continue;
            }

            $output->writeln(
                sprintf('<info>Channel #%s: %s processing.</info>', $channel->getId(), $channel->getName())
            );

            $em = $this->doctrineHelper->getEntityManager($customerIdentityClass);
            $entities = $this->getEntitiesByChannel($channel, $ids);
            $entitiesToSave = [];

            foreach ($entities as $entity) {
                if ($this->getAnalyticsBuilder()->build($entity)) {
                    $entitiesToSave[] = $entity;

                    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $output->writeln(
                            sprintf(
                                '    %s #%s.',
                                $customerIdentityClass,
                                $this->getDoctrineHelper()->getSingleEntityIdentifier($entity)
                            )
                        );
                    }
                }

                if (count($entitiesToSave) % self::FLUSH_BATCH_SIZE === 0) {
                    $em->flush($entitiesToSave);
                    $entitiesToSave = [];
                }
            }

            $em->flush($entitiesToSave);

            $output->writeln(
                sprintf(
                    '    <info>Done. %s/%s updated.</info>',
                    count($entitiesToSave),
                    $entities->count()
                )
            );
        }
    }

    /**
     * @return DoctrineHelper
     */
    protected function getDoctrineHelper()
    {
        if (!$this->doctrineHelper) {
            $this->doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        }

        return $this->doctrineHelper;
    }

    /**
     * @return AnalyticsBuilder
     */
    protected function getAnalyticsBuilder()
    {
        if (!$this->analyticsBuilder) {
            $this->analyticsBuilder = $this->getContainer()->get('orocrm_analytics.builder');
        }

        return $this->analyticsBuilder;
    }

    /**
     * @param int $channelId
     *
     * @return BufferedQueryResultIterator|Channel[]
     */
    protected function getChannels($channelId = null)
    {
        $className = $this->getContainer()->getParameter('orocrm_channel.entity.class');
        $qb = $this->getDoctrineHelper()
            ->getEntityRepository($className)
            ->createQueryBuilder('c');

        $where = $qb->expr()->andX(
            $qb->expr()->eq('c.status', ':status')
        );
        $qb->setParameter('status', Channel::STATUS_ACTIVE);

        if ($channelId) {
            $where->add($qb->expr()->eq('c.id', ':id'));
            $qb->setParameter('id', $channelId);
        }

        $qb->andWhere($where);

        return new BufferedQueryResultIterator($qb);
    }

    /**
     * @param Channel $channel
     * @param array $ids
     *
     * @return BufferedQueryResultIterator|CustomerIdentity[]
     */
    protected function getEntitiesByChannel(Channel $channel, $ids = [])
    {
        $qb = $this->getDoctrineHelper()
            ->getEntityRepository($channel->getCustomerIdentity())
            ->createQueryBuilder('e');

        $qb
            ->where($qb->expr()->eq('e.dataChannel', ':dataChannel'))
            ->setParameter('dataChannel', $channel->getId());

        if ($ids) {
            $qb
                ->andWhere($qb->expr()->in('e.id', ':ids'))
                ->setParameter('ids', $ids);
        }

        return new BufferedQueryResultIterator($qb);
    }

    /**
     * @return string
     */
    protected function getAnalyticsAwareInterface()
    {
        return $this->getContainer()->getParameter('orocrm_analytics.model.analytics_aware_interface');
    }
}
