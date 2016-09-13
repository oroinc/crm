<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\GuestCustomerDataConverter;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCartReader;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

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
        $customer = $this->findExistingEntity($order->getCustomer());
        $customerOriginId = $order->getCustomer()->getOriginId();
        if (!$customer && $customerOriginId) {
            $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

            $isProcessingAllowed = false;
        }

        // Do not try to load cart if bridge does not installed
        /** @var MagentoSoapTransport $transport */
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
     * Get existing entity
     * As guest customer entity not exist in Magento as separate entity and saved in order
     * find guest by customer email
     *
     * @param object $entity
     * @param array $searchContext
     * @return null|object
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        if ($entity instanceof Customer && !$entity->getOriginId() && !$entity->getId() && $this->existingEntity) {
            return $this->findExistingCustomerByContext($this->existingEntity);
        }

        return parent::findExistingEntity($entity, $searchContext);
    }
}
