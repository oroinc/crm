<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

class ContextReader extends AbstractReader
{
    /** @var string */
    protected $contextKey;

    /**
     * @param string $contextKey
     */
    public function setContextKey($contextKey)
    {
        $this->contextKey = $contextKey;
    }

    /**
     * @var array[]
     */
    protected $entities;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->entities) {
            return null;
        }

        return array_shift($this->entities);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->contextKey) {
            throw new \InvalidArgumentException('Context key is missing');
        }

        $this->entities = (array)$this->stepExecution->getJobExecution()->getExecutionContext()->get($this->contextKey);
        $this->stepExecution->getJobExecution()->getExecutionContext()->remove($this->contextKey);
    }
}
