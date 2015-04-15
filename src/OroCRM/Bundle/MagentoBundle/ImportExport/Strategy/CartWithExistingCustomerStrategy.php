<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

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
            $this->appendDataToContext(self::CONTEXT_CART_POST_PROCESS, $this->context->getValue('itemData'));

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
        $isProcessingAllowed = true;

        $customerOriginId = $cart->getCustomer()->getOriginId();
        if (!$this->customer && $customerOriginId) {
            $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
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
