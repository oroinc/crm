<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Order;

class CreditMemoStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if ($entity instanceof CreditMemo) {
            $this->processOrder($entity);
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            if (!$entity->getImportedAt()) {
                $entity->setImportedAt($now);
            }
            $entity->setSyncedAt($now);
        }

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param CreditMemo $entity
     */
    protected function processOrder(CreditMemo $entity)
    {
        if ($order = $entity->getOrder()) {
            if (!$order->getId() && !$order->getIncrementId() && $order->getOriginId()) {
                /** @var Order $existingOrder */
                $existingOrder = $this->databaseHelper->findOneBy(
                    Order::class,
                    [
                        'channel' => $entity->getChannel(),
                        'originId' => $order->getOriginId(),
                    ]
                );
                if ($existingOrder) {
                    $entity->setOrder($existingOrder);
                }
            }
        }
    }
}
