<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderStrategy extends AbstractImportStrategy
{
    /**
     * @var Order
     */
    protected $existingEntity;

    /**
     * @param Order $entity
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
     * @param Order $entity
     *
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if (!$entity->getUpdatedAt() && $entity->getCreatedAt()) {
            $entity->setUpdatedAt($entity->getCreatedAt());
        }

        /** @var Order $order */
        $this->processCart($entity);
        $this->processAddresses($this->existingEntity, $entity);
        $this->processItems($this->existingEntity, $entity);
        $this->processCustomer($entity, $entity->getCustomer());

        $this->existingEntity = null;

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Order $entity
     */
    protected function saveOriginIdContext($entity)
    {
        if ($entity instanceof Order) {
            $postProcessIds = (array)$this->getExecutionContext()->get(self::CONTEXT_POST_PROCESS_IDS);
            $postProcessIds[ClassUtils::getClass($entity)][] = $entity->getIncrementId();
            $this->getExecutionContext()->put(self::CONTEXT_POST_PROCESS_IDS, $postProcessIds);
        }
    }

    /**
     * @param Order $order
     * @param Customer $customer
     */
    protected function processCustomer(Order $order, Customer $customer = null)
    {
        if ($customer instanceof Customer) {
            // now customer orders subtotal calculation support only one currency.
            // also we do not take into account order refunds due to magento does not bring subtotal data
            // customer currency needs on customer's grid to format lifetime value.
            $customer->setCurrency($order->getCurrency());
        }
        $order->setCustomer($customer);
    }

    /**
     * If cart exists then add relation to it,
     * do nothing otherwise
     *
     * @param Order $entity
     */
    protected function processCart(Order $entity)
    {
        $cart = $entity->getCart();

        if ($cart) {
            $statusClass = MagentoConnectorInterface::CART_STATUS_TYPE;
            /** @var CartStatus $purchasedStatus */
            $this->databaseHelper->findOneBy($statusClass, ['name' => CartStatus::STATUS_PURCHASED]);
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
                $this->strategyHelper->importEntity(
                    $existingAddress,
                    $address,
                    ['id', 'region', 'country', 'owner', 'types']
                );
                $address = $existingAddress;
            }

            $this->addressHelper->updateAddressCountryRegion($address, $mageRegionId);
            if (!$address->getCountry()) {
                $entityToUpdate->getAddresses()->offsetUnset($k);
                continue;
            }

            $this->addressHelper->updateAddressTypes($address);

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
                return !in_array($item->getOriginId(), $importedOriginIds, true);
            }
        );
        foreach ($deleted as $item) {
            $entityToUpdate->getItems()->removeElement($item);
        }
    }
}
