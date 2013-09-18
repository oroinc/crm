<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import;

use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

class AddOrReplaceStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function process($entity)
    {
        // TODO: Implement process() method.
    }

    /**
     * Strategy label to use on frontend
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Add or Replace';
    }
}
