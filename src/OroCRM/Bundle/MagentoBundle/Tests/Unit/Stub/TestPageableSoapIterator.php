<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Stub;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator;
use Psr\Log\LoggerInterface;

class TestPageableSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        // TODO: Implement getIdFieldName() method.
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        // TODO: Implement getEntityIds() method.
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        // TODO: Implement getEntity() method.
    }
}
