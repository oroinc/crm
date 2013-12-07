<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CartStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRM\Bundle\MagentoBundle\Entity\Cart';

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
            ['id', 'store', 'cartItems', 'customer']
        );

        if (!$newEntity->getStore()->getId()) {
            $newEntity->setStore(
                $this->storeStrategy->process($entity->getStore())
            );
        }

        $this
            ->updateCustomer($newEntity, $entity->getCustomer())
            ->updateCartItems($newEntity, $entity->getCartItems());

        // TODO: update addresses, if exists

        $this->validateAndUpdateContext($newEntity);

        return $newEntity;
    }

    /**
     * Assign existing customer, if not found - set null
     *
     * @param Cart     $newCart
     * @param Customer $customer
     *
     * @return $this
     */
    protected function updateCustomer(Cart $newCart, Customer $customer)
    {
        $updatedCustomer = $this->getEntityByCriteria(
            ['originId' => $customer->getOriginId(), 'channel' => $customer->getChannel()],
            $customer
        );

        if ($updatedCustomer) {
        } else {
            $newCart->setCustomer(null);
        }

        return $this;
    }

    /**
     * @param Cart $newCart
     * @param      $cartItems
     *
     * @return $this
     */
    protected function updateCartItems(Cart $newCart, $cartItems)
    {
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
