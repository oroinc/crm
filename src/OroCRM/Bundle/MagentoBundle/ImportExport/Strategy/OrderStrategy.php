<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Region;

use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;

class OrderStrategy extends AbstractImportStrategy
{
    const CONTEXT_ORDER_POST_PROCESS_IDS = 'postProcessOrderIds';

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

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getImportedAt()) {
            $entity->setImportedAt($now);
        }
        $entity->setSyncedAt($now);

        /** @var Order $order */
        $this->processCart($entity);
        $this->processItems($entity);
        $this->processAddresses($entity);
        $this->processCustomer($entity, $entity->getCustomer());

        $this->existingEntity = null;

        $this->appendDataToContext(self::CONTEXT_ORDER_POST_PROCESS_IDS, $entity->getIncrementId());

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Order $order
     * @param Customer $customer
     */
    protected function processCustomer(Order $order, Customer $customer = null)
    {
        if (!$customer || !$customer->getId()) {
            $customer = $this->findExistingCustomerByContext($order);
        }

        if ($customer instanceof Customer) {
            // now customer orders subtotal calculation support only one currency.
            // also we do not take into account order refunds due to magento does not bring subtotal data
            // customer currency needs on customer's grid to format lifetime value.
            $customer->setCurrency($order->getCurrency());
        }
        $order->setCustomer($customer);

        if ($order->getCart()) {
            $order->getCart()->setCustomer($customer);
        }
    }

    /**
     * Add order customer email to customer search context
     *
     * {@inheritdoc}
     */
    protected function getEntityCustomerSearchContext($order)
    {
        /** @var Order $order */
        $searchContext = parent::getEntityCustomerSearchContext($order);
        $searchContext['email'] = $order->getCustomerEmail();

        return $searchContext;
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
            $purchasedStatus = $this->databaseHelper
                ->findOneBy($statusClass, ['name' => CartStatus::STATUS_PURCHASED]);
            if ($purchasedStatus) {
                $cart->setStatus($purchasedStatus);
            }
        }

        $entity->setCart($cart);
    }

    /**
     * @param Order $order
     *
     * @return OrderStrategy
     */
    protected function processItems(Order $order)
    {
        foreach ($order->getItems() as $item) {
            $item->setOwner($order->getOrganization());
            $item->setOrder($order);
        }

        return $this;
    }

    /**
     * @param Order $order
     *
     * @return OrderStrategy
     */
    protected function processAddresses(Order $order)
    {
        /** @var OrderAddress $address */
        foreach ($order->getAddresses() as $address) {
            $address->setOwner($order);
        }

        return $this;
    }

    /**
     * BC layer to find existing collection items by old identity filed values
     *
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = parent::findExistingEntity($entity, $searchContext);

        if (!$existingEntity && $entity instanceof OrderAddress) {
            /** @var OrderAddress $existingEntity */
            $existingEntity = $this->existingEntity->getAddresses()
                ->filter(
                    function (OrderAddress $address) use ($entity) {
                        $isMatched = true;
                        $fieldsToMatch = ['street', 'city', 'postalCode', 'country', 'region'];

                        foreach ($fieldsToMatch as $fieldToMatch) {
                            $addressValue = $this->getPropertyAccessor()->getValue($address, $fieldToMatch);
                            $entityValue = $this->getPropertyAccessor()->getValue($entity, $fieldToMatch);
                            $isMatched = $isMatched && ($addressValue === $entityValue);
                        }

                        return $isMatched;
                    }
                )
                ->first();

            if ($existingEntity && $entity->getOriginId()) {
                $existingEntity->setOriginId($entity->getOriginId());
            }
        }

        if ($entity instanceof OrderItem && is_null($entity->getName())) {
            //name can't be null, so to avoid import job failing empty string is used
            $entity->setName('');
        }

        if (!$existingEntity && $entity instanceof Region) {
            $existingEntity = $this->findRegionEntity($entity);
        }

        return $existingEntity;
    }

    /**
     * Add special identifier for entities not existing in Magento
     * Add customer Email to search context for processing related entity Guest Customer for Order
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (is_a($entityName, 'OroCRM\Bundle\MagentoBundle\Entity\Customer', true)
            && empty($identityValues['originId'])
            && $this->existingEntity
        ) {
            $identityValues['email'] = $this->existingEntity->getCustomerEmail();
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
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
            $searchContext['email'] = $this->existingEntity->getCustomerEmail();
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }
}
