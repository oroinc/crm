<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\CartWithExistingCustomerStrategy;

class ContextCartReader extends AbstractContextReader
{
    /**
     * @return Cart[]
     */
    protected function getEntities()
    {
        return (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(CartWithExistingCustomerStrategy::CONTEXT_CART_POST_PROCESS);
    }
}
