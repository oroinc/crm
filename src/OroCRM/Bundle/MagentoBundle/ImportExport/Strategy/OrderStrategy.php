<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderStrategy extends BaseStrategy
{
    /** @var array */
    protected static $attributesToUpdateManual = ['id', 'store', 'items', 'customer', 'addresses'];

    /** @var StoreStrategy */
    protected $storeStrategy;

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $criteria       = ['incrementId' => $entity->getIncrementId(), 'channel' => $entity->getChannel()];
        $existingEntity = $this->getEntityByCriteria($criteria, $entity);

        if ($existingEntity) {
            $this->strategyHelper->importEntity($existingEntity, $entity, self::$attributesToUpdateManual);
        } else {
            $existingEntity = $entity;
        }

        $this->processStore($existingEntity);
        $this->processCustomer($existingEntity);
        $this->processCart($existingEntity);
        $this->processAddresses($existingEntity, $entity);

        // check errors, update context increments
        return $this->validateAndUpdateContext($existingEntity);
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
        $customer = $entity->getCustomer();
        $criteria = ['originId' => $customer['originId'], 'channel' => $entity->getChannel()];

        /** @var Customer|null $customer */
        $customer = $this->getEntityByCriteria($criteria, 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer');
        $entity->setCustomer($customer);
    }

    /**
     * @param Order $entity
     */
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
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }
}
