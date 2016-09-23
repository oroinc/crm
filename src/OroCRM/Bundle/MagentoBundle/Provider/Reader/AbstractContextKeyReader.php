<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

abstract class AbstractContextKeyReader extends AbstractReader
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
     * @var object[]
     */
    protected $entities = [];

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
}
