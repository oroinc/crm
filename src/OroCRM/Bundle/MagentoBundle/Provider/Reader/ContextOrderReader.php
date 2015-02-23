<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;

class ContextOrderReader extends AbstractReader
{
    /**
     * @var array[]
     */
    protected $orders;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->orders) {
            return null;
        }

        return array_shift($this->orders);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $this->orders = $this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS) ?: [];
    }
}
