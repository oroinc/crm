<?php

namespace OroCRM\Bundle\AnalyticsBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class RFMCategoryListener
{
    /**
     * @var RFMMetricStateManager
     */
    protected $metricStateManager;

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
     * @param string $categoryClass
     * @param string $channelClass
     */
    public function __construct(RFMMetricStateManager $metricStateManager, $categoryClass, $channelClass)
    {
        $this->metricStateManager = $metricStateManager;
        $this->categoryClass = $categoryClass;
        $this->channelClass = $channelClass;
    }

    /**
     * @param OnFlushEventArgs $args
     */
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

        foreach ($this->channelsToRecalculate as $channel) {
            $this->metricStateManager->resetMetrics($channel);
            $this->metricStateManager->scheduleRecalculation($channel, false);
        }

        foreach ($this->channelsToDrop as $channel) {
            $this->metricStateManager->resetMetrics($channel);
        }

        if ($this->channelsToRecalculate || $this->channelsToDrop) {
            $uow->computeChangeSets();
        }
    }

    /**
     * @param object $entity
     */
    protected function handleEntity($entity)
    {
        /** @var RFMMetricCategory $entity */
        if ($entity instanceof $this->categoryClass) {
            $channel = $entity->getChannel();

            $this->channelsToRecalculate[$channel->getId()] = $channel;
        }

        /** @var Channel $entity */
        if ($entity instanceof $this->channelClass) {
            $data = $entity->getData();
            if (empty($data[ChannelTypeExtension::RFM_REQUIRE_DROP_KEY])) {
                return;
            }

            unset($data[ChannelTypeExtension::RFM_REQUIRE_DROP_KEY]);
            $entity->setData($data);

            $this->channelsToDrop[$entity->getId()] = $entity;
        }
    }
}
