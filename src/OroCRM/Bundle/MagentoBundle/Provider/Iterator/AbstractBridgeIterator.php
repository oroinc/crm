<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

abstract class AbstractBridgeIterator extends AbstractPageableSoapIterator
{
    const DEFAULT_PAGE_SIZE = 50;

    /** @var int */
    protected $currentPage = 1;

    /**
     * Load entities ids list
     *
     * @return true|null true when there are ids retrieved
     */
    protected function findEntitiesToProcess()
    {
        $result = parent::findEntitiesToProcess();
        $this->currentPage++;

        return $result;
    }

    /**
     * @return int
     */
    protected function getCurrentPage()
    {
        return $this->currentPage;
    }
}
