<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderStrategy extends BaseStrategy
{
    /** @var array */
    protected static $attributesToUpdateManual = [
            'id',
            'store',
            'items',
            'customer',
            'addresses',
            'workflowItem',
            'workflowStep'
        ];

    /** @var StoreStrategy */
    protected $storeStrategy;

    /**
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function process($importingOrder)
    {
        $criteria = ['incrementId' => $importingOrder->getIncrementId(), 'channel' => $importingOrder->getChannel()];
        $order    = $this->getEntityByCriteria($criteria, $importingOrder);

        if ($order) {
            $this->strategyHelper->importEntity($order, $importingOrder, self::$attributesToUpdateManual);
        } else {
            $order = $importingOrder;
        }
        /** @var Order $order */
        $this->processStore($order);
        $this->processCustomer($order);
        $this->processCart($order);
        $this->processAddresses($order, $importingOrder);
        $this->processItems($order, $importingOrder);

        // check errors, update context increments
        return $this->validateAndUpdateContext($order);
    }

    /**
     * @param Order $entity
     */
    protected function processStore(Order $entity)
    {
        $entity->setStore($this->storeStrategy->process($entity->getStore()));
    }

    /**
     * If customer exists then add relation to it,
     * do nothing otherwise
     *
     * @param Order $entity
     */
    protected function processCustomer(Order $entity)
    {
        // customer could be array if comes new order or object if comes from DB
        $customerId = is_object($entity->getCustomer())
            ? $entity->getCustomer()->getOriginId()
            : $entity->getCustomer()['originId'];

        $criteria = ['originId' => $customerId, 'channel' => $entity->getChannel()];

        /** @var Customer|null $customer */
        $customer = $this->getEntityByCriteria($criteria, MagentoConnectorInterface::CUSTOMER_TYPE);
        $entity->setCustomer($customer);
    }

    /**
     * If cart exists then add relation to it,
     * do nothing otherwise
     *
     * @param Order $entity
     */
    protected function processCart(Order $entity)
    {
        // cart could be array if comes new order or object if comes from DB
        $cartId = is_object($entity->getCart())
            ? $entity->getCart()->getOriginId()
            : $entity->getCart()['originId'];

        $criteria = ['originId' => $cartId, 'channel' => $entity->getChannel()];

        /** @var Cart|null $cart */
        $cart = $this->getEntityByCriteria($criteria, MagentoConnectorInterface::CART_TYPE);

        if ($cart) {
            $statusClass     = MagentoConnectorInterface::CART_STATUS_TYPE;
            $purchasedStatus = $this->strategyHelper->getEntityManager($statusClass)->find($statusClass, 'purchased');
            if ($purchasedStatus) {
                $cart->setStatus($purchasedStatus);
            }
        }

        $entity->setCart($cart);
    }

    /**
     * @param Order $entityToUpdate
     * @param Order $entityToImport
     */
    protected function processAddresses(Order $entityToUpdate, Order $entityToImport)
    {
        /** @var OrderAddress $address */
        foreach ($entityToImport->getAddresses() as $k => $address) {
            if (!$address->getCountry()) {
                // skip addresses without country, we cant save it
                $entityToUpdate->getAddresses()->offsetUnset($k);
                continue;
            }
            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $existingAddress = $entityToUpdate->getAddresses()->get($k);
            if ($existingAddress) {
                $this->strategyHelper->importEntity($existingAddress, $address, ['id', 'region', 'country']);
                $address = $existingAddress;
            }

            $this->updateAddressCountryRegion($address, $mageRegionId);
            if (!$address->getCountry()) {
                $entityToUpdate->getAddresses()->offsetUnset($k);
                continue;
            }

            $this->updateAddressTypes($address);

            $address->setOwner($entityToUpdate);
            $entityToUpdate->getAddresses()->set($k, $address);
        }
    }

    /**
     * @param Order $entityToUpdate
     * @param Order $entityToImport
     */
    protected function processItems(Order $entityToUpdate, Order $entityToImport)
    {
        $importedOriginIds = $entityToImport->getItems()->map(
            function (OrderItem $item) {
                return $item->getOriginId();
            }
        )->toArray();

        // insert new and update existing items
        /** @var OrderItem $item - imported order item */
        foreach ($entityToImport->getItems() as $item) {
            $originId = $item->getOriginId();

            $existingItem = $entityToUpdate->getItems()->filter(
                function (OrderItem $item) use ($originId) {
                    return $item->getOriginId() == $originId;
                }
            )->first();

            if ($existingItem) {
                $this->strategyHelper->importEntity($existingItem, $item, ['id', 'order']);
                $item = $existingItem;
            }

            if (!$item->getOrder()) {
                $item->setOrder($entityToUpdate);
            }

            if (!$entityToUpdate->getItems()->contains($item)) {
                $entityToUpdate->getItems()->add($item);
            }
        }

        // delete order items that not exists in remote order
        $deleted = $entityToUpdate->getItems()->filter(
            function (OrderItem $item) use ($importedOriginIds) {
                return !in_array($item->getOriginId(), $importedOriginIds);
            }
        );
        foreach ($deleted as $item) {
            $entityToUpdate->getItems()->remove($item);
        }
    }
}
