<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberStrategy extends AbstractImportStrategy
{
    /**
     * @var ChannelHelper
     */
    protected $channelHelper;

    /**
     * @var StoreStrategy
     */
    protected $storeStrategy;

    /**
     * @param ChannelHelper $channelHelper
     */
    public function setChannelHelper(ChannelHelper $channelHelper)
    {
        $this->channelHelper = $channelHelper;
    }

    /**
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }

    /**
     * @param NewsletterSubscriber $entity
     * @return NewsletterSubscriber
     */
    protected function afterProcessEntity($entity)
    {
        $this->processStore($entity);
        $this->processDataChannel($entity);
        $this->processChangeStatusAt($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param NewsletterSubscriber $entity
     */
    protected function processChangeStatusAt(NewsletterSubscriber $entity)
    {
        if (!$entity->getChangeStatusAt()) {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $entity
                ->setCreatedAt($now)
                ->setUpdatedAt($now);
        } else {
            $entity->setUpdatedAt($entity->getChangeStatusAt());

            if (!$entity->getId()) {
                $entity->setCreatedAt($entity->getChangeStatusAt());
            }
        }
    }

    /**
     * @param NewsletterSubscriber $entity
     */
    protected function processStore(NewsletterSubscriber $entity)
    {
        $store = $entity->getStore();
        if ($entity->getStore()) {
            $store = $this->storeStrategy->process($store);

            $entity->setStore($store);
        }
    }

    /**
     * @param NewsletterSubscriber $entity
     */
    protected function processDataChannel(NewsletterSubscriber $entity)
    {
        if ($entity->getChannel()) {
            $dataChannel = $this->channelHelper->getChannel($entity->getChannel());
            if ($dataChannel) {
                $entity->setDataChannel($dataChannel);
            }
        }
    }
}
