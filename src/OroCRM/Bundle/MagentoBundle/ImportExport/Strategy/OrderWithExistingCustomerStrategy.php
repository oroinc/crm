<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

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
        $customer = $this->findExistingCustomer($order);
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

        if (!$customer && $order->getIsGuest() && $transport->getGuestCustomerSync()) {
            $this->appendDataToContext(
                'postProcessGuestCustomers',
                GuestCustomerDataConverter::extractCustomersValues((array)$this->context->getValue('itemData'))
            );

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
    }

    /**
     * Get existing customer or existing guest customer
     *
     * @param Order $order
     * @return null|Customer
     */
    protected function findExistingCustomer(Order $order)
    {
        $customer = $order->getCustomer();
        $customerOriginId = $customer->getOriginId();

        /** @var Customer|null $existingEntity */
        if ($customer->getId() || $customerOriginId) {
            $existingEntity = $this->findExistingEntity($customer);
        } else {
            $existingEntity = $this->databaseHelper->findOneBy(
                'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                [
                    'email' => $order->getCustomerEmail(),
                    'channel' => $customer->getChannel()
                ]
            );
        }

        return $existingEntity;
    }
}
