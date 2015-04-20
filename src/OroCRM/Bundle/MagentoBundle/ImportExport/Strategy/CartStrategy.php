<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;

class CartStrategy extends AbstractImportStrategy
{
    /**
     * @var Cart
     */
    protected $existingEntity;

    /**
     * @param Cart $entity
     *
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        $this->existingEntity = $this->databaseHelper->findOneByIdentity($entity);
        if (!$this->existingEntity) {
            $this->existingEntity = $entity;
        }

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param Cart $entity
     *
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $hasContactInfo = ($entity->getBillingAddress() && $entity->getBillingAddress()->getPhone())
            || $entity->getEmail();

        if (!$entity->getItemsCount()) {
            $this->context->incrementErrorEntriesCount();
            $this->logger->debug(
                sprintf('Cart ID: %d was skipped because it does not have items', $entity->getOriginId())
            );

            return null;
        } elseif (!$hasContactInfo) {
            $this->context->incrementErrorEntriesCount();
            $this->logger->debug(
                sprintf('Cart ID: %d was skipped because lack of contact info', $entity->getOriginId())
            );

            return null;
        }

        $this
            ->updateCustomer($entity)
            ->updateAddresses($entity)
            ->updateCartItems($entity->getCartItems())
            ->updateCartStatus($entity);

        $this->existingEntity = null;

        return parent::afterProcessEntity($entity);
    }

    /**
     * Update Customer email
     *
     * @param Cart $cart
     *
     * @return CartStrategy
     */
    protected function updateCustomer(Cart $cart)
    {
        $customer = $cart->getCustomer();
        if ($customer && !$customer->getEmail()) {
            $customer->setEmail($cart->getEmail());
        }

        return $this;
    }

    /**
     * @param Collection $cartItems imported items
     *
     * @return CartStrategy
     */
    protected function updateCartItems(Collection $cartItems)
    {
        $importedOriginIds = $cartItems->map(
            function (CartItem $item) {
                return $item->getOriginId();
            }
        )->toArray();

        // insert new and update existing items
        /** $item - imported cart item */
        foreach ($cartItems as $item) {
            $originId = $item->getOriginId();

            $existingItem = $this->existingEntity->getCartItems()->filter(
                function (CartItem $item) use ($originId) {
                    return $item->getOriginId() === $originId;
                }
            )->first();

            if ($existingItem) {
                $this->strategyHelper->importEntity($existingItem, $item, ['id', 'cart']);
                $item = $existingItem;
            }

            if (!$item->getCart()) {
                $item->setCart($this->existingEntity);
            }

            if (!$this->existingEntity->getCartItems()->contains($item)) {
                $this->existingEntity->getCartItems()->add($item);
            }
        }

        // delete cart items that not exists in remote cart
        $deletedCartItems = $this->existingEntity->getCartItems()->filter(
            function (CartItem $item) use ($importedOriginIds) {
                return !in_array($item->getOriginId(), $importedOriginIds, true);
            }
        );
        foreach ($deletedCartItems as $item) {
            $this->existingEntity->getCartItems()->removeElement($item);
        }

        return $this;
    }

    /**
     * @param Cart $entity
     *
     * @return CartStrategy
     */
    protected function updateAddresses(Cart $entity)
    {
        $addresses = ['shippingAddress', 'billingAddress'];
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($addresses as $addressName) {
            /** @var CartAddress $address */
            $address = $propertyAccessor->getValue($entity, $addressName);

            if (!$address) {
                continue;
            }

            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;
            $originAddressId = $address->getOriginId();

            /** @var CartAddress $existingAddress */
            $existingAddress = $propertyAccessor->getValue($this->existingEntity, $addressName);
            if ($existingAddress && $existingAddress->getOriginId() == $originAddressId) {
                $this->strategyHelper->importEntity(
                    $existingAddress,
                    $address,
                    ['id', 'region', 'country']
                );
                $address = $existingAddress;
            }

            $this->addressHelper->updateAddressCountryRegion($address, $mageRegionId);
            if ($address->getCountry()) {
                $propertyAccessor->setValue($entity, $addressName, $address);
            } else {
                $propertyAccessor->setValue($entity, $addressName, null);
            }
        }

        return $this;
    }

    /**
     * Update cart status
     *
     * @param Cart $cart
     *
     * @return CartStrategy
     */
    protected function updateCartStatus(Cart $cart)
    {
        // allow to modify status only for "open" carts
        // because magento can only expire cart, so for different statuses this useless
        if ($this->existingEntity->getStatus()->getName() !== CartStatus::STATUS_OPEN) {
            $status = $this->existingEntity->getStatus();
        } else {
            $status = $cart->getStatus();
        }

        $cart->setStatus($status);

        return $this;
    }
}
