<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderWithExistingCustomerStrategy extends OrderStrategy implements StepExecutionAwareInterface
{
    const CONTEXT_ORDER_POST_PROCESS = 'postProcessOrders';

    /**
     * @var Customer|null
     */
    protected $customer;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function process($importingOrder)
    {
        $this->customer = null;
        if (!$this->isProcessingAllowed($importingOrder)) {
            $postProcessOrders = $this->getExecutionContext()->get(self::CONTEXT_ORDER_POST_PROCESS);
            $postProcessOrders[] = $importingOrder;
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

    /**
     * @return ExecutionContext
     */
    protected function getExecutionContext()
    {
        if (!$this->stepExecution) {
            throw new \InvalidArgumentException('Execution context is not configured');
        }

        return $this->stepExecution->getJobExecution()->getExecutionContext();
    }
}
