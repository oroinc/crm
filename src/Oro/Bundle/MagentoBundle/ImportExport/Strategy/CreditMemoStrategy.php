<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Entity\Order;

class CreditMemoStrategy extends AbstractImportStrategy
{
    const CONTEXT_CREDIT_MEMO_POST_PROCESS_IDS = 'postProcessCreditMemoIds';

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if ($entity instanceof CreditMemo) {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            if (!$entity->getImportedAt()) {
                $entity->setImportedAt($now);
            }
            $entity->setSyncedAt($now);
            $this->processItems($entity);
            $this->appendDataToContext(self::CONTEXT_CREDIT_MEMO_POST_PROCESS_IDS, $entity->getIncrementId());
        }

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param CreditMemo $creditMemo
     *
     * @return $this
     */
    protected function processItems(CreditMemo $creditMemo)
    {
        foreach ($creditMemo->getItems() as $item) {
            $item->setOwner($creditMemo->getOrganization());
            $item->setParent($creditMemo);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Order) {
            //for credit memo we have only order origin id to look for
            $existingEntity = $this->databaseHelper->findOneBy(
                Order::class,
                [
                    'channel' =>  $this->context->getOption('channel'),
                    'originId' => $entity->getOriginId()
                ]
            );
        } else {
            $existingEntity = parent::findExistingEntity($entity, $searchContext);
        }

        return $existingEntity;
    }
}
