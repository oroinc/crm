<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

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
        if (!$this->hasContactInfo($entity)) {
            return null;
        }

        $this
            ->updateCustomer($entity)
            ->updateAddresses($entity)
            ->updateCartItems($entity)
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
     * @param Cart $cart
     *
     * @return CartStrategy
     */
    protected function updateCartItems(Cart $cart)
    {
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItem->setCart($cart);
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
     * @param Cart $entity
     * @return null
     */
    protected function hasContactInfo(Cart $entity)
    {
        $hasContactInfo = ($entity->getBillingAddress() && $entity->getBillingAddress()->getPhone())
            || $entity->getEmail();

        if (!$entity->getItemsCount()) {
            $this->context->incrementErrorEntriesCount();
            $this->logger->debug(
                sprintf('Cart ID: %d was skipped because it does not have items', $entity->getOriginId())
            );

            return false;
        } elseif (!$hasContactInfo) {
            $this->context->incrementErrorEntriesCount();
            $this->logger->debug(
                sprintf('Cart ID: %d was skipped because lack of contact info', $entity->getOriginId())
            );

            return false;
        }

        return true;
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
