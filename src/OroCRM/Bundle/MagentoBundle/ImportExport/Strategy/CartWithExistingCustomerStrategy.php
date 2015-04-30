<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

class CartWithExistingCustomerStrategy extends CartStrategy
{
    const CONTEXT_CART_POST_PROCESS = 'postProcessCarts';

    /**
     * {@inheritdoc}
     */
    public function process($importingCart)
    {
        if (!$this->hasContactInfo($importingCart)) {
            return null;
        }

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
        $customer = $this->findExistingEntity($cart->getCustomer());
        $isProcessingAllowed = true;

        $customerOriginId = $cart->getCustomer()->getOriginId();
        if (!$customer && $customerOriginId) {
            $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $customerOriginId);

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
    }
}
