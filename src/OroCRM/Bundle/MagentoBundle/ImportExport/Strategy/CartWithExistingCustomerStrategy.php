<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CartWithExistingCustomerStrategy extends CartStrategy
{
    const CONTEXT_CART_POST_PROCESS = 'postProcessCarts';

    /** @var Customer */
    protected $customer;

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
        $this->customer = $this->findExistingEntity($cart->getCustomer());

        return $this->customer && $this->customer->getId();
    }

    /**
     * {@inheritdoc}
     */
    protected function updateCustomer(Cart $newCart, Customer $customer = null)
    {
        $customerToProcess = $this->customer ?: $customer;

        return parent::updateCustomer($newCart, $customerToProcess);
    }
}
