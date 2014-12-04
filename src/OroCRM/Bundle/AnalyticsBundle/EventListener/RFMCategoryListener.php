<?php

namespace OroCRM\Bundle\AnalyticsBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
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
     * @var Channel[]
     */
    protected $channels = [];

    /**
     * @param RFMMetricStateManager $metricStateManager
     * @param string $categoryClass
     */
    public function __construct(RFMMetricStateManager $metricStateManager, $categoryClass)
    {
        $this->metricStateManager = $metricStateManager;
        $this->categoryClass = $categoryClass;
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

        if (!$this->channels) {
            return;
        }

        foreach ($this->channels as $channel) {
            $this->metricStateManager->resetMetrics($channel);
            $this->metricStateManager->scheduleRecalculation($channel, false);
        }

        $uow->computeChangeSets();
    }

    /**
     * @param object $entity
     */
    protected function handleEntity($entity)
    {
        /** @var RFMMetricCategory $entity */
        if ($entity instanceof $this->categoryClass) {
            $channel = $entity->getChannel();

            $this->channels[$channel->getId()] = $channel;
        }
    }
}
