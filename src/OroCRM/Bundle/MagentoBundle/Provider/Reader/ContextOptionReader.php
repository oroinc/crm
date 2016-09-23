<?php
namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;

class ContextOptionReader extends AbstractContextKeyReader
{
    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->contextKey) {
            throw new \InvalidArgumentException('Context key is missing');
        }

        $this->entities = [$context->getOption($this->contextKey)];
    }
}
