<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes\ChainProcessor as OrderNotesProcessor;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;
use Oro\Bundle\MagentoBundle\Provider\Connector\MagentoConnectorInterface;

/**
 * Import strategy for sales orders.
 */
class OrderStrategy extends AbstractImportStrategy
{
    const CONTEXT_ORDER_POST_PROCESS_IDS = 'postProcessOrderIds';
    const CONTEXT_DETACHED_ENTITIES_IDS = 'detachedEntitiesIds';

    /**
     * @var Order
     */
    protected $existingEntity;

    /** @var GuestCustomerStrategyHelper */
    protected $guestCustomerStrategyHelper;

    /**
     * @var OrderNotesProcessor
     */
    private $orderNotesProcessor;

    /**
     * @param GuestCustomerStrategyHelper $strategyHelper
     */
    public function setGuestCustomerStrategyHelper(GuestCustomerStrategyHelper $strategyHelper)
    {
        $this->guestCustomerStrategyHelper = $strategyHelper;
    }

    /**
     * @param OrderNotesProcessor $orderNotesProcessor
     */
    public function setOrderNotesProcessor(OrderNotesProcessor $orderNotesProcessor)
    {
        $this->orderNotesProcessor = $orderNotesProcessor;
    }

    /**
     * @param Order $entity
     *
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        if ($this->isDetachedEntity($entity)) {
            return null;
        }

        $this->existingEntity = $this->databaseHelper->findOneByIdentity($entity);
        if (!$this->existingEntity) {
            $this->existingEntity = $entity;
        }

        foreach ($entity->getAddresses() as $address) {
            if ($address->getRegion() && $address->getCountry()) {
                $originId = $address->getOriginId();
                // at this point imported address region have code equal to region_id in magento db field
                $this->addressHelper->addMageRegionId(
                    OrderAddress::class,
                    $originId,
                    $address->getRegion()->getCode()
                );
                /**
                 * We must run this method here because it set regionText to address to prevent error of
                 * "Not found entity State". Real Region will be set in "afterProcessEntity" method
                 */
                $this->addressHelper->updateRegionByMagentoRegionId($address, $originId, true);
            }
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
        $this->orderNotesProcessor->processNotes($entity);

        $this->addressHelper->resetMageRegionIdCache(OrderAddress::class);
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
        foreach ($order->getAddresses() as $address) {
            if ($address->getCountry()) {
                $this->addressHelper->updateRegionByMagentoRegionId($address, $address->getOriginId());
                $address->setCountryText(null);
            }

            $address->setOwner($order);
        }

        return $this;
    }

    /**
     * BC layer to find existing collection items by old identity filed values
     *
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
        if (is_a($entityName, Customer::class, true)
            && empty($identityValues['originId'])
            && $this->existingEntity
        ) {
            $identityValues += $this->getEntityCustomerSearchContext($this->existingEntity);
            $identityValues = $this->guestCustomerStrategyHelper->updateIdentityValuesByCustomerOrParentEntity(
                $this->existingEntity,
                $identityValues
            );
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
            $searchContext += $this->getEntityCustomerSearchContext($this->existingEntity);
            $searchContext = $this->guestCustomerStrategyHelper->updateIdentityValuesByCustomerOrParentEntity(
                $this->existingEntity,
                $searchContext
            );
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }

    /**
     * {@inheritdoc}
     */
    protected function processValidationErrors($entity, array $validationErrors)
    {
        parent::processValidationErrors($entity, $validationErrors);

        $this->appendDataToContext(self::CONTEXT_DETACHED_ENTITIES_IDS, $entity->getIncrementId());
    }

    /**
     * Check if entity was detached
     *
     * @param object $entity
     *
     * @return bool
     */
    private function isDetachedEntity($entity)
    {
        $detachedEntitiesIds = (array) $this->getExecutionContext()->get(self::CONTEXT_DETACHED_ENTITIES_IDS);

        if (in_array($entity->getIncrementId(), $detachedEntitiesIds, true)) {
            return true;
        }

        return false;
    }
}
