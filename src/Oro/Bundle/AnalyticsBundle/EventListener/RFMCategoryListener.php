<?php

namespace Oro\Bundle\AnalyticsBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;

class RFMCategoryListener
{
    /**
     * @var RFMMetricStateManager
     */
    protected $metricStateManager;

    /**
     * @var CalculateAnalyticsScheduler
     */
    protected $calculateAnalyticsScheduler;

    /**
     * @var string
     */
    protected $categoryClass;

    /**
     * @var string
     */
    protected $channelClass;

    /**
     * @var Channel[]
     */
    protected $channelsToRecalculate = [];

    /**
     * @var Channel[]
     */
    protected $channelsToDrop = [];

    /**
     * @param RFMMetricStateManager $metricStateManager
     * @param CalculateAnalyticsScheduler $calculateAnalyticsScheduler
     * @param string $categoryClass
     * @param string $channelClass
     */
    public function __construct(
        RFMMetricStateManager $metricStateManager,
        CalculateAnalyticsScheduler $calculateAnalyticsScheduler,
        $categoryClass,
        $channelClass
    ) {
        $this->metricStateManager = $metricStateManager;
        $this->categoryClass = $categoryClass;
        $this->channelClass = $channelClass;
        $this->calculateAnalyticsScheduler = $calculateAnalyticsScheduler;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->handleEntity($entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->handleEntity($entity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->handleEntity($entity);
        }
    }

    public function onChannelSucceedSave(ChannelSaveEvent $event)
    {
        foreach ($this->channelsToDrop as $channel) {
            $this->metricStateManager->resetMetrics($channel);
        }

        foreach ($this->channelsToRecalculate as $channel) {
            if (array_key_exists(spl_object_hash($channel), $this->channelsToDrop)) {
                continue;
            }

            $this->metricStateManager->resetMetrics($channel);
            $this->calculateAnalyticsScheduler->scheduleForChannel($channel->getId());
        }

        $this->channelsToDrop = [];
        $this->channelsToRecalculate = [];
    }

    /**
     * @param object $entity
     */
    protected function handleEntity($entity)
    {
        /** @var RFMMetricCategory $entity */
        if ($entity instanceof $this->categoryClass) {
            $channel = $entity->getChannel();

            $this->channelsToRecalculate[spl_object_hash($channel)] = $channel;
        }

        /** @var Channel $entity */
        if ($entity instanceof $this->channelClass) {
            $data = $entity->getData();
            if (empty($data[RFMAwareInterface::RFM_REQUIRE_DROP_KEY])) {
                return;
            }

            unset($data[RFMAwareInterface::RFM_REQUIRE_DROP_KEY]);
            $entity->setData($data);

            $this->channelsToDrop[spl_object_hash($entity)] = $entity;
        }
    }
}
