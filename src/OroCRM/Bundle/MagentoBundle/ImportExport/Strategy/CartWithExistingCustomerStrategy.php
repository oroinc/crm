<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class CartWithExistingCustomerStrategy extends CartStrategy implements StepExecutionAwareInterface
{
    const CONTEXT_CART_POST_PROCESS = 'postProcessCarts';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var Customer */
    protected $customer;

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
    public function process($importingCart)
    {
        $this->customer = null;
        if (!$this->isProcessingAllowed($importingCart)) {
            $postProcessCarts = (array)$this->getExecutionContext()->get(self::CONTEXT_CART_POST_PROCESS);
            $postProcessCarts[] = $this->context->getValue('itemData');
            $this->getExecutionContext()->put(self::CONTEXT_CART_POST_PROCESS, $postProcessCarts);

            return null;
        }

        return parent::process($importingCart);
    }

    /**
     * @param Cart $cart
     * @return bool
     */
    protected function isProcessingAllowed(Cart $cart)
    {
        // customer could be array if comes new order or object if comes from DB
        $customerId = is_object($cart->getCustomer())
            ? $cart->getCustomer()->getOriginId()
            : $cart->getCustomer()['originId'];

        if (!$customerId) {
            return true;
        }

        /** @var Customer|null $customer */
        $this->customer = $this->getEntityByCriteria(
            ['originId' => $customerId, 'channel' => $cart->getChannel()],
            MagentoConnectorInterface::CUSTOMER_TYPE
        );

        return (bool)$this->customer;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateCustomer(Cart $newCart, Customer $customer)
    {
        $customerToProcess = $this->customer ?: $customer;

        return parent::updateCustomer($newCart, $customerToProcess);
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
