<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

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
}
