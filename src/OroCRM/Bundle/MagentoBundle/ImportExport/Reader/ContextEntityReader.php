<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

class ContextEntityReader extends AbstractReader
{
    const CONTEXT_KEY = 'entity';

    /**
     * @var bool
     */
    protected $processed = false;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if ($this->processed) {
            return null;
        }

        $entity = $this->getContext()->getOption('entity');

        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf('Object expected, "%s" given', gettype($entity))
            );
        }

        $this->processed = true;

        return $entity;
    }
}
