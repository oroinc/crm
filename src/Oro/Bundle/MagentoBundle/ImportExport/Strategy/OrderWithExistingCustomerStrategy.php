<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\ImportExport\Converter\GuestCustomerDataConverter;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextCartReader;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

class OrderWithExistingCustomerStrategy extends OrderStrategy
{
    const CONTEXT_ORDER_POST_PROCESS = 'postProcessOrders';

    /**
     * @param Order $importingOrder
     *
     * {@inheritdoc}
     */
    public function process($importingOrder)
    {
        if (!$this->isProcessingAllowed($importingOrder)) {
            $this->appendDataToContext(self::CONTEXT_ORDER_POST_PROCESS, $this->context->getValue('itemData'));

            return null;
        }

        return parent::process($importingOrder);
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function isProcessingAllowed(Order $order)
    {
        $isProcessingAllowed = true;
        $customer = $this->findExistingCustomer($order);
        $customerOriginId = $order->getCustomer()->getOriginId();
        if (!$customer && $customerOriginId) {
            $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

            $isProcessingAllowed = false;
        }

        // Do not try to load cart if bridge does not installed
        /** @var MagentoTransport $transport */
        $channel = $this->databaseHelper->findOneByIdentity($order->getChannel());
        $transport = $channel->getTransport();
        if ($transport->getIsExtensionInstalled()) {
            $cart = $this->findExistingEntity($order->getCart());
            $cartOriginId = $order->getCart()->getOriginId();
            if (!$cart && $cartOriginId) {
                $this->appendDataToContext(ContextCartReader::CONTEXT_POST_PROCESS_CARTS, $cartOriginId);

                $isProcessingAllowed = false;
            }
        }

        /**
         * If Order created with registered customer but registered customer was deleted in Magento
         * before it was synced order will not have connection to the customer
         * Customer for such orders should be processed as guest if Guest Customer synchronization is allowed
         */
        if (!$customer && !$customerOriginId && $transport->getGuestCustomerSync()) {
            $this->appendDataToContext(
                'postProcessGuestCustomers',
                GuestCustomerDataConverter::extractCustomersValues((array)$this->context->getValue('itemData'))
            );

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
    }

    /**
     * Get existing registered customer or existing guest customer
     * If customer not found by Identifier and customer is guest or was deleted on Magento side
     * find existing customer using entity data for entities containing customer like Order and Cart
     *
     * @param Order $entity
     *
     * @return null|Customer
     */
    protected function findExistingCustomer(Order $entity)
    {
        $existingEntity = null;
        $customer = $entity->getCustomer();

        if ($customer->getId() || $customer->getOriginId()) {
            $existingEntity = parent::findExistingEntity($customer);
        }
        if (!$existingEntity && !$customer->getOriginId()) {
            $existingEntity = $this->findGuestCustomer($entity);
        }

        return $existingEntity;
    }

    /**
     * Get existing customer entity by Identifier or using entity data
     *
     * @param object $entity
     * @param array $searchContext
     * @return null|object
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        if ($entity instanceof Customer && !$entity->getOriginId() && $this->existingEntity) {
            return $this->findGuestCustomer($this->existingEntity, $searchContext);
        }

        return parent::findExistingEntity($entity, $searchContext);
    }

    /**
     * @param Order $entity
     * @param array $searchContext
     *
     * @return null|Customer
     */
    protected function findGuestCustomer(Order $entity, array $searchContext = [])
    {
        $searchContext += $this->getEntityCustomerSearchContext($entity);
        return $this->guestCustomerStrategyHelper->findExistingGuestCustomerByContext(
            $entity,
            $searchContext
        );
    }
}
