<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

abstract class AbstractBridgeIterator extends AbstractPageableSoapIterator
{
    const DEFAULT_PAGE_SIZE = 50;

    /** @var int */
    protected $currentPage = 1;

    /** @var \stdClass[] Entities buffer got from pageable remote */
    protected $entityBuffer = null;

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

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $result = $this->entityBuffer[$id];

        $this->addDependencyData($result);

        return ConverterUtils::objectToArray($result);
    }
}
