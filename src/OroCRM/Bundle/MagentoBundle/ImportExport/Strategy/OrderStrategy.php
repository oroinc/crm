<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

class OrderStrategy extends BaseStrategy
{
    /** @var array */
    protected static $attributesToUpdateManual = ['id', 'store', 'items', 'customer', 'addresses'];

    /** @var StoreStrategy */
    protected $storeStrategy;

    public function __construct(ImportStrategyHelper $strategyHelper, StoreStrategy $storeStrategy)
    {
        parent::__construct($strategyHelper);
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
        $this->processItems($order);

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
        $customer = $this->getEntityByCriteria($criteria, CustomerStrategy::ENTITY_NAME);
        $entity->setCustomer($customer);
    }

    /**
     * @param Order $entity
     */
    protected function processCart(Order $entity)
    {
        /** @var Cart $cart */
        $cart = $this->getEntityByCriteria(
            ['originId' => $entity->getCart()['originId'], 'channel' => $entity->getChannel()],
            CartStrategy::ENTITY_NAME
        );

        if ($cart) {
            $cart->setStatus(Cart::STATUS_CONVERTED);
            $entity->setCart($cart);
        } else {
            // @TODO decide to import new one or not
            $entity->setCart(null);
        }
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
            $this->updateAddressTypes($address);

            $address->setOwner($entityToUpdate);
            $entityToUpdate->getAddresses()->set($k, $address);
        }
    }

    /**
     * @param Order $order
     */
    protected function processItems(Order $order)
    {

    }
}
