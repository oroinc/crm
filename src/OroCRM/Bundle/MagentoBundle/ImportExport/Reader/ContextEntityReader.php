<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Reader;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
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
     * @param ContextRegistry $contextRegistry
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ContextRegistry $contextRegistry, DoctrineHelper $doctrineHelper)
    {
        parent::__construct($contextRegistry);
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $entity = $this->getContext()->getOption('entity');
        if (!$entity) {
            return null;
        }

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
}
