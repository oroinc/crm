<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;

class ContextOrderReader extends AbstractContextReader
{
    /**
     * @return Order[]
     */
    protected function getEntities()
    {
        return (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS);
    }
}
