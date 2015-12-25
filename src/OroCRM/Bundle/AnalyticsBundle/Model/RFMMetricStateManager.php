<?php

namespace OroCRM\Bundle\AnalyticsBundle\Model;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class RFMMetricStateManager extends StateManager
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * @var string
     */
    protected $channelClass;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string         $interface
     * @param string         $channelClass
     */
    public function __construct(DoctrineHelper $doctrineHelper, $interface, $channelClass)
    {
        parent::__construct($doctrineHelper);

        $this->interface    = $interface;
        $this->channelClass = $channelClass;
    }

    /**
     * @param Channel $channel
     */
    public function resetMetrics(Channel $channel = null)
    {
        $criteria = [];

        if ($channel) {
            $criteria = ['id' => $this->doctrineHelper->getSingleEntityIdentifier($channel)];
        }

        /** @var Channel[] $channels */
        $channels = $this->doctrineHelper->getEntityRepository($this->channelClass)->findBy($criteria);

        $channelsByCustomerIdentity = [];
        foreach ($channels as $channel) {
            $customerIdentity = $channel->getCustomerIdentity();

            if (!$customerIdentity) {
                continue;
            }

            if (!in_array($this->interface, class_implements($customerIdentity), true)) {
                continue;
            }

            $channelsByCustomerIdentity[$customerIdentity][] = $this->doctrineHelper
                ->getSingleEntityIdentifier($channel);
        }

        foreach ($channelsByCustomerIdentity as $className => $channelIds) {
            $this->executeResetQuery($className, $channelIds);
        }
    }

    /**
     * @param string $className
     * @param array  $ids
     */
    protected function executeResetQuery($className, $ids)
    {
        if (!$ids) {
            return;
        }

        $qb = $this->doctrineHelper
            ->getEntityManager($className)
            ->createQueryBuilder()
            ->update($className, 'e');

        foreach (RFMMetricCategory::$types as $type) {
            $qb
                ->set(sprintf('e.%s', $type), sprintf(':%s', $type))
                ->setParameter($type, null);
        }

        $qb
            ->where($qb->expr()->in('e.dataChannel', ':dataChannels'))
            ->setParameter('dataChannels', $ids);

        $qb->getQuery()->execute();
    }

    /**
     * @param Channel $channel
     */
    public function scheduleRecalculation(Channel $channel = null)
    {
        if ($channel) {
            $argument = sprintf('--channel=%s', $channel->getId());

            if ($this->isJobRunning($argument)) {
                return;
            }

            $isActiveChannel = $channel->getStatus() === Channel::STATUS_ACTIVE;
            $channelData = $channel->getData();
            $rfmEnabled = !empty($channelData[RFMAwareInterface::RFM_STATE_KEY]);

            if (!$isActiveChannel || !$rfmEnabled) {
                return;
            }
        }

        if ($this->getJob()) {
            return;
        }

        $args = [];
        if ($channel) {
            $argument = sprintf('--channel=%s', $channel->getId());
            $channelJob = $this->getJob($argument);
            if ($channelJob) {
                return;
            }

            $args = [$argument];
        }

        $job = new Job(CalculateAnalyticsCommand::COMMAND_NAME, $args);
        $em = $this->doctrineHelper->getEntityManager($job);

        if (!$channel) {
            $channelJobs = $this->getJob('--channel');

            if ($channelJobs) {
                foreach ($channelJobs as $channelJob) {
                    $em->remove($channelJob);
                }
            }
        }

        $em->persist($job);
        $em->flush($job);
    }
}
