<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

class NewsletterSubscriberStrategy extends AbstractImportStrategy
{
    /**
     * @param NewsletterSubscriber $entity
     * @return NewsletterSubscriber
     */
    protected function afterProcessEntity($entity)
    {
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
}
