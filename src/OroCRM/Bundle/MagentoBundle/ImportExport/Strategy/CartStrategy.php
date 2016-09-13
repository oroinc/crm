<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\GuestCustomerDataConverter;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

class CartStrategy extends AbstractImportStrategy
{
    /**
     * @var Cart
     */
    protected $existingEntity;

    /**
     * @var array
     */
    protected $existingCartItems;

    /**
     * @param Cart $entity
     *
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        $this->existingEntity = $this->databaseHelper->findOneByIdentity($entity);
        if ($this->existingEntity) {
            $this->existingCartItems = $this->existingEntity->getCartItems()->toArray();
        } else {
            $this->existingEntity = $entity;
        }

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param Cart $entity
     *
     * {@inheritdoc}
     */
    public function process($entity)
    {
        if (!$this->isProcessingAllowed($entity)) {
            $this->appendDataToContext(
                CartWithExistingCustomerStrategy::CONTEXT_CART_POST_PROCESS,
                $this->context->getValue('itemData')
            );

            return null;
        }

        return parent::process($entity);
    }

    /**
     * @param Cart $cart
     * @return bool
     */
    protected function isProcessingAllowed(Cart $cart)
    {
        $isProcessingAllowed = true;

        if ($cart->getCustomer()) {
            $customer = $this->findExistingEntity($cart->getCustomer());
            $customerOriginId = $cart->getCustomer()->getOriginId();
            if (!$customer && $customerOriginId) {
                $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

                $isProcessingAllowed = false;
            }

            /**
             * If registered customer add items to cart in Magento
             * but after customer was deleted in Magento before customer and cart were synced
             * Cart will not have connection to the customer
             * Customer for such Carts should be processed as guest if Guest Customer synchronization is allowed
             */
            if (!$customer && !$customerOriginId) {
                /** @var Channel $channel */
                $channel = $this->databaseHelper->findOneByIdentity($cart->getChannel());
                /** @var MagentoSoapTransport $transport */
                $transport = $channel->getTransport();
                if ($transport->getGuestCustomerSync()) {
                    $this->appendDataToContext(
                        'postProcessGuestCustomers',
                        GuestCustomerDataConverter::extractCustomersValues((array)$this->context->getValue('itemData'))
                    );

                    $isProcessingAllowed = false;
                }
            }
        }

        return $isProcessingAllowed;
    }

    /**
     * @param Cart $entity
     *
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if ($this->existingEntity->getStatus()->getName() === CartStatus::STATUS_OPEN) {
            $this->updateRemovedCartItems($entity);
        }

        if (!$this->hasContactInfo($entity)) {
            return null;
        }

        $this
            ->updateCustomer($entity)
            ->updateAddresses($entity)
            ->updateCartItems($entity)
            ->updateCartStatus($entity);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getImportedAt()) {
            $entity->setImportedAt($now);
        }
        $entity->setSyncedAt($now);

        $this->existingEntity = null;
        $this->existingCartItems = null;

        return parent::afterProcessEntity($entity);
    }

    /**
     * Update removed cart items - set `removed` field to true if cart item was removed from a cart
     *
     * @param Cart $entity
     */
    protected function updateRemovedCartItems(Cart $entity)
    {
        if ((int)$entity->getItemsQty() === 0) {
            foreach ($entity->getCartItems() as $cartItem) {
                if (!$cartItem->isRemoved()) {
                    $cartItem->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                    $cartItem->setRemoved(true);
                }
            }
        } elseif ($this->existingCartItems) {
            $existingCartItems = new ArrayCollection($this->existingCartItems);
            $newCartItems = $entity->getCartItems();

            foreach ($existingCartItems as $existingCartItem) {
                if (!$newCartItems->contains($existingCartItem)) {
                    if (!$existingCartItem->isRemoved()) {
                        $existingCartItem->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                        $existingCartItem->setRemoved(true);
                    }
                }
            }
        }
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
            $cartItem->setOwner($cart->getOrganization());
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

        foreach ($addresses as $addressName) {
            /** @var CartAddress $address */
            $address = $this->getPropertyAccessor()->getValue($entity, $addressName);

            if (!$address) {
                continue;
            }

            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;
            $this->addressHelper->updateAddressCountryRegion($address, $mageRegionId);
            if ($address->getCountry()) {
                $this->getPropertyAccessor()->setValue($entity, $addressName, $address);
            } else {
                $this->getPropertyAccessor()->setValue($entity, $addressName, null);
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

        if (!$hasContactInfo) {
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

    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Region) {
            /** @var \OroCRM\Bundle\MagentoBundle\Entity\Region $magentoRegion */
            $magentoRegion = $this->databaseHelper->findOneBy(
                'OroCRM\Bundle\MagentoBundle\Entity\Region',
                [
                    'regionId' => $entity->getCode()
                ]
            );
            if ($magentoRegion) {
                $existingEntity = $this->databaseHelper->findOneBy(
                    'Oro\Bundle\AddressBundle\Entity\Region',
                    [
                        'combinedCode' => $magentoRegion->getCombinedCode()
                    ]
                );
            }
        } elseif ($entity instanceof Customer
            && !$entity->getOriginId()
            && !$entity->getId()
            && $this->existingEntity
        ) {
            /**
             * Get existing customer entity
             * As guest customer entity not exist in Magento as separate entity and saved in order
             * find guest by customer email
             */
            return $this->findExistingCustomerByContext($this->existingEntity);
        } else {
            $existingEntity = parent::findExistingEntity($entity, $searchContext);
        }

        return $existingEntity;
    }

    /**
     * Add cart customer email to customer search context
     *
     * {@inheritdoc}
     */
    protected function getEntityCustomerSearchContext($cart)
    {
        /** @var Cart $cart */
        $searchContext = parent::getEntityCustomerSearchContext($cart);
        $searchContext['email'] = $cart->getEmail();

        return $searchContext;
    }
}
