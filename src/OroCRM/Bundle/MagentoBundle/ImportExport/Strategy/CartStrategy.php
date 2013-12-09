<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;

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
    public function process($newEntity)
    {
        $existingEntity = $this->getEntityByCriteria(
            ['originId' => $newEntity->getOriginId(), 'channel' => $newEntity->getChannel()],
            $newEntity
        );

        if ($existingEntity) {
            $this->strategyHelper->importEntity($existingEntity, $newEntity, ['id', 'store', 'cartItems', 'customer']);
        } else {
            $existingEntity = $newEntity;
        }

        if (!$existingEntity->getStore() || !$existingEntity->getStore()->getId()) {
            $existingEntity->setStore(
                $this->storeStrategy->process($newEntity->getStore())
            );
        }

        $this->updateCustomer($existingEntity, $newEntity->getCustomer())
            ->updateAddresses($existingEntity)
            ->updateCartItems($existingEntity, $newEntity->getCartItems());

        $this->validateAndUpdateContext($existingEntity);

        return $existingEntity;
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
        $existingCustomer = $this->getEntityByCriteria(
            ['originId' => $customer->getOriginId(), 'channel' => $customer->getChannel()],
            $customer
        );

        if ($existingCustomer) {
            $newCart->setCustomer($existingCustomer);
        } else {
            $newCart->setCustomer(null);
        }

        return $this;
    }

    /**
     * @param Cart            $cart
     * @param ArrayCollection $cartItems imported items
     *
     * @return $this
     */
    protected function updateCartItems(Cart $cart, ArrayCollection $cartItems)
    {
        $importedOriginIds = $cartItems->map(
            function ($item) {
                return $item->getOriginId();
            }
        )->toArray();

        // insert new and update existing items
        /** $item - imported cart item */
        foreach ($cartItems as $item) {
            $originId = $item->getOriginId();

            $existingItem = $cart->getCartItems()->filter(
                function ($item) use ($originId) {
                    return $item->getOriginId() == $originId;
                }
            )->first();

            if ($existingItem) {
                $this->strategyHelper->importEntity($existingItem, $item, ['id', 'cart']);
                $item = $existingItem;
            }

            if (!$item->getCart()) {
                $item->setCart($cart);
            }

            if (!$cart->getCartItems()->contains($item)) {
                $cart->getCartItems()->add($item);
            }
        }

        // delete cart items that not exists in remote cart
        $deletedCartItems = $cart->getCartItems()->filter(
            function ($item) use ($importedOriginIds) {
                return !in_array($item->getOriginId(), $importedOriginIds);
            }
        );
        foreach ($deletedCartItems as $item) {
            $cart->getCartItems()->remove($item);
        }

        return $this;
    }

    /**
     * @param Cart $newCart
     *
     * @return $this
     */
    protected function updateAddresses(Cart $newCart)
    {
        // TODO: implement update addresses, if exists

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
