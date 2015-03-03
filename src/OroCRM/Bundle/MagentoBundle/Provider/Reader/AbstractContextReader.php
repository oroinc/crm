<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

abstract class AbstractContextReader extends AbstractReader
{
    /**
     * @var array[]
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->data) {
            return null;
        }

        return array_shift($this->data);
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $this->data = $this->getEntities();
    }

    /**
     * @return object[]
     */
    abstract protected function getEntities();
}
