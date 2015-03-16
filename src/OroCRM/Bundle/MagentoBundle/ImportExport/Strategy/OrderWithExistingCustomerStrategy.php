<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderWithExistingCustomerStrategy extends OrderStrategy
{
    const CONTEXT_ORDER_POST_PROCESS = 'postProcessOrders';

    /**
     * @var Customer|null
     */
    protected $customer;

    /**
     * {@inheritdoc}
     */
    public function process($importingOrder)
    {
        $this->customer = null;
        if (!$this->isProcessingAllowed($importingOrder)) {
            $postProcessOrders = (array)$this->getExecutionContext()->get(self::CONTEXT_ORDER_POST_PROCESS);
            $postProcessOrders[] = $this->context->getValue('itemData');
            $this->getExecutionContext()->put(self::CONTEXT_ORDER_POST_PROCESS, $postProcessOrders);

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
        // customer could be array if comes new order or object if comes from DB
        $customerId = is_object($order->getCustomer())
            ? $order->getCustomer()->getOriginId()
            : $order->getCustomer()['originId'];

        if (!$customerId) {
            return true;
        }

        /** @var Customer|null $customer */
        $this->customer = $this->getEntityByCriteria(
            ['originId' => $customerId, 'channel' => $order->getChannel()],
            MagentoConnectorInterface::CUSTOMER_TYPE
        );

        return (bool)$this->customer;
    }

    /**
     * {@inheritdoc}
     */
    protected function processCustomer(Order $entity)
    {
        if ($this->customer) {
            $this->updateCustomer($entity, $this->customer);
        } else {
            parent::processCustomer($entity);
        }
    }
}
