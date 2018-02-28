<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\ImportExport\Converter\GuestCustomerDataConverter;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
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
     * @var GuestCustomerStrategyHelper
     */
    protected $guestCustomerStrategyHelper;

    /**
     * @param GuestCustomerStrategyHelper $strategyHelper
     */
    public function setGuestCustomerStrategyHelper(GuestCustomerStrategyHelper $strategyHelper)
    {
        $this->guestCustomerStrategyHelper = $strategyHelper;
    }

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
            $this->existingCartItems = [];
            $this->existingEntity = $entity;
        }

        /** @var CartAddress[] $addressData */
        $addressData = array_filter([
            $this->existingEntity->getBillingAddress(),
            $this->existingEntity->getShippingAddress()
        ]);

        foreach ($addressData as $address) {
            if ($address->getRegion() && $address->getCountry()) {
                $originId = $address->getOriginId();
                // at this point imported address region have code equal to region_id in magento db field
                $this->addressHelper->addMageRegionId(
                    CartAddress::class,
                    $originId,
                    $address->getRegion()->getCode()
                );
                /**
                 * We must run this method here because it set regionText to address to prevent error of
                 * "Not found entity". Real Region will be set in "afterProcessEntity" method
                 */
                $this->addressHelper->updateRegionByMagentoRegionId($address, $originId, true);
            }
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
            $customer = $this->findExistingCustomer($cart);
            $customerOriginId = $cart->getCustomer()->getOriginId();
            if (!$customer) {
                if ($customerOriginId) {
                    $this->appendDataToContext(
                        ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS,
                        $customerOriginId
                    );

                    $isProcessingAllowed = false;
                } else {
                    /**
                     * If registered customer add items to cart in Magento
                     * but after customer was deleted in Magento before customer and cart were synced
                     * Cart will not have connection to the customer
                     * Customer for such Carts should be processed as guest if Guest Customer synchronization is allowed
                     */

                    /** @var Channel $channel */
                    $channel = $this->databaseHelper->findOneByIdentity($cart->getChannel());
                    /** @var MagentoTransport $transport */
                    $transport = $channel->getTransport();
                    if ($transport->getGuestCustomerSync()) {
                        $this->appendDataToContext(
                            'postProcessGuestCustomers',
                            GuestCustomerDataConverter::extractCustomersValues(
                                (array)$this->context->getValue('itemData')
                            )
                        );

                        $isProcessingAllowed = false;
                    }
                }
            }
        }

        return $isProcessingAllowed;
    }

    /**
     * Get existing registered customer or existing guest customer
     * If customer not found by Identifier and customer is guest or was deleted on Magento side
     * find existing customer using entity data for entities containing customer like Order and Cart
     *
     * @param Cart $entity
     *
     * @return null|Customer
     */
    protected function findExistingCustomer($entity)
    {
        $existingEntity = null;
        $customer = $entity->getCustomer();

        if ($customer->getId() || $customer->getOriginId()) {
            $existingEntity = parent::findExistingEntity($customer);
        }

        if (!$existingEntity && !$customer->getOriginId()) {
            $searchContext = $this->getEntityCustomerSearchContext($entity);
            $existingEntity = $this->guestCustomerStrategyHelper->findExistingGuestCustomerByContext(
                $customer,
                $searchContext
            );
        }

        return $existingEntity;
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
        } else {
            $entity->setStatus($this->existingEntity->getStatus());
        }

        if (!$this->hasContactInfo($entity)) {
            return null;
        }

        $this
            ->updateCustomer($entity)
            ->updateAddresses($entity)
            ->updateCartItems($entity);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getImportedAt()) {
            $entity->setImportedAt($now);
        }
        $entity->setSyncedAt($now);

        $this->addressHelper->resetMageRegionIdCache(CartAddress::class);
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
        $newCartItems = $entity->getCartItems();
        if ((int)$entity->getItemsQty() === 0) {
            foreach ($newCartItems as $cartItem) {
                $this->setCartItemRemoved($cartItem);
            }

            return;
        }

        foreach ($this->existingCartItems as $existingCartItem) {
            if (!$newCartItems->contains($existingCartItem)) {
                $this->setCartItemRemoved($existingCartItem);
            }
        }
    }

    /**
     * @param CartItem $cartItem
     */
    protected function setCartItemRemoved(CartItem $cartItem)
    {
        if (!$cartItem->isRemoved()) {
            $cartItem->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $cartItem->setRemoved(true);
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
            if ($address) {
                $this->getPropertyAccessor()->setValue($entity, $addressName, $this->getUpdatedAddress($address));
            }
        }

        return $this;
    }

    /**
     * @param CartAddress $address
     *
     * @return null|CartAddress
     */
    protected function getUpdatedAddress(CartAddress $address)
    {
        $this->addressHelper->updateAddressCountryRegion($address, $address->getOriginId());
        if (!$address->getCountry()) {
            return null;
        }
        $address->setCountryText(null);

        return $address;
    }

    /**
     * @param Cart $entity
     * @return boolean
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
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Region) {
            /** @var \Oro\Bundle\MagentoBundle\Entity\Region $existingEntity */
            $existingEntity = $this->findRegionEntity($entity);
        } elseif ($entity instanceof Customer && !$entity->getOriginId() && $this->existingEntity) {
            /**
             * Get existing customer entity
             * As guest customer entity not exist in Magento as separate entity and saved in order
             * find guest by customer email
             */
            $searchContext += $this->getEntityCustomerSearchContext($this->existingEntity);
            $existingEntity = $this->guestCustomerStrategyHelper->findExistingGuestCustomerByContext(
                $entity,
                $searchContext
            );
        } else {
            $existingEntity = parent::findExistingEntity($entity, $searchContext);
        }

        return $existingEntity;
    }

    /**
     * Add special search context for entities not existing in Magento
     * Add customer Email to search context for Order related entity Guest Customer
     *
     * @param object $entity
     * @param string $entityClass
     * @param array $searchContext
     * @return array|null
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        if ($entity instanceof Customer && !$entity->getOriginId() && $this->existingEntity) {
            $searchContext += $this->getEntityCustomerSearchContext($this->existingEntity);
            $searchContext = $this->guestCustomerStrategyHelper->updateIdentityValuesByCustomerOrParentEntity(
                $entity,
                $searchContext
            );
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
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
