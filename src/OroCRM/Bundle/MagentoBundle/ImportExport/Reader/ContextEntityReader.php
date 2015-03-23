<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

class ContextEntityReader extends AbstractReader
{
    const CONTEXT_KEY = 'entity';

    /**
     * @var object
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->entity) {
            return null;
        }

        $entity = $this->entity;
        $this->entity = null;

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $entity = $context->getOption('entity');


        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf('Object expected, "%s" given', gettype($entity))
            );
        }

        $this->entity = $entity;
    }
}
