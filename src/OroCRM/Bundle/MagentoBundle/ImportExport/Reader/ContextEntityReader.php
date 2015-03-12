<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Reader\AbstractReader;

class ContextEntityReader extends AbstractReader
{
    const CONTEXT_KEY = 'entity';

    /**
     * @var array
     */
    protected $processed = [];

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $entity = $this->getContext()->getOption('entity');
        $entityIdentifier = $this->doctrineHelper->getSingleEntityIdentifier($entity);

        if (!empty($this->processed[$entityIdentifier])) {
            return null;
        }

        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf('Object expected, "%s" given', gettype($entity))
            );
        }

        $this->processed[$entityIdentifier] = true;

        return $entity;
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     * @return ContextEntityReader
     */
    public function setDoctrineHelper($doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;

        return $this;
    }
}
