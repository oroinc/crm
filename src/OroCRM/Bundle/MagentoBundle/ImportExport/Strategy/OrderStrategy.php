<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRM\Bundle\MagentoBundle\Entity\Cart';

    /** @var StoreStrategy */
    protected $storeStrategy;

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $existingEntity = $this->getEntityByCriteria(
            ['incrementId' => $entity->getIncrementId(), 'channel' => $entity->getChannel()],
            $entity
        );

        if ($existingEntity) {
            $this->strategyHelper->importEntity(
                $existingEntity,
                $entity,
                ['id', 'store', 'items', 'customer', 'addresses']
            );
        } else {
            $existingEntity = $entity;
        }

        if (!$entity->getStore() || !$entity->getStore()->getId()) {
            $entity->setStore($this->storeStrategy->process($entity->getStore()));
        }

        $this->processCustomer($existingEntity);
        $this->processCart($existingEntity);
        $this->processAddresses($existingEntity, $entity);

        $this->validateAndUpdateContext($existingEntity);

        return $existingEntity;
    }

    protected function processCustomer(Order $entity)
    {
        $existingCustomer = $this->getEntityByCriteria(
            ['originId' => $entity->getOwner()['originId'], 'channel' => $entity->getChannel()],
            'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer'
        );

        if ($existingCustomer) {
            $entity->setOwner($existingCustomer);
        } else {
            $entity->setOwner(null);
        }
    }

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

    protected function processAddresses(Order $entityToUpdate, Order $entityToImport)
    {
        /** @var OrderAddress $address */
        foreach ($entityToImport->getAddresses() as $k => $address) {
            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $existingAddress = $entityToUpdate->getAddresses()->get($k);
            if ($existingAddress) {
                $this->strategyHelper->importEntity($existingAddress, $address, ['id', 'region', 'country']);
                $address = $existingAddress;
            }

            $this
                ->updateAddressCountryRegion($address, $mageRegionId)
                ->updateAddressTypes($address);

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
