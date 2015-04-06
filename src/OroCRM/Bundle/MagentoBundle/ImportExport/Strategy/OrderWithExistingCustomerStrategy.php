<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

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
        $this->customer = $this->findExistingEntity($order->getCustomer());

        return $this->customer && $this->customer->getId();
    }
}
