<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class ContextReader extends AbstractContextKeyReader
{
    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->contextKey) {
            throw new \InvalidArgumentException('Context key is missing');
        }

        $this->entities = (array)$this->stepExecution->getJobExecution()->getExecutionContext()->get($this->contextKey);
    }
}
