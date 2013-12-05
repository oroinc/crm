<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

class CartStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Cart';

    /** @var StoreStrategy */
    protected $storeStrategy;

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $newEntity = $this->findAndReplaceEntity(
            $entity,
            self::ENTITY_NAME,
            'originId',
            ['id', 'store', 'cartItems']
        );

        $newEntity->setStore(
            $this->storeStrategy->process($entity->getStore())
        );

        $this
            ->updateCartItems($newEntity, $entity->getCartItems());

        // update addresses
        // update customer link if exists

        $this->validateAndUpdateContext($newEntity);

        return $newEntity;
    }

    /**
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }
}
