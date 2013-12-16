<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Order;

class OrderStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRM\Bundle\MagentoBundle\Entity\Cart';

    /** @var StoreStrategy */
    protected $storeStrategy;

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $existingEntity = $this->getEntityByCriteria(
            ['incrementId' => $entity->getIncrementId(), 'channel' => $entity->getChannel()],
            $entity
        );

        if ($existingEntity) {
            $this->strategyHelper->importEntity(
                $existingEntity,
                $entity,
                ['id', 'store', 'items', 'customer', 'addresses']
            );
            $entity = $existingEntity;
        }

        if (!$entity->getStore() || !$entity->getStore()->getId()) {
            $entity->setStore($this->storeStrategy->process($entity->getStore()));
        }

        $this->processCustomer($entity)
            ->processCart($entity);

        $this->validateAndUpdateContext($entity);

        return $entity;
    }


    protected function processCustomer(Order $entity)
    {
        $existingCustomer = $this->getEntityByCriteria(
            ['originId' => $entity->getOwner()['originId'], 'channel' => $entity->getChannel()],
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer'
        );

        if ($existingCustomer) {
            $entity->setOwner($existingCustomer);
        } else {
            $entity->setOwner(null);
        }


        return $this;
    }

    protected function processCart(Order $entity)
    {
        $existingCart = $this->getEntityByCriteria(
            ['originId' => $entity->getCart()['originId'], 'channel' => $entity->getChannel()],
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart'
        );

        if ($existingCart) {
            $entity->setCart($existingCart);
        } else {
            // @TODO decide to import new one or not
            $entity->setCart(null);
        }

        return $this;
    }

    /**
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }
}
