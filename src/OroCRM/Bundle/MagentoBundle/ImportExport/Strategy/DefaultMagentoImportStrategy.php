<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class DefaultMagentoImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        if ($this->databaseHelper->getIdentifier($entity)) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }

        return parent::afterProcessEntity($entity);
    }
}
