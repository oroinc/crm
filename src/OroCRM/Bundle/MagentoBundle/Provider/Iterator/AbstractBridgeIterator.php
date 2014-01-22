<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

abstract class AbstractBridgeIterator extends AbstractPageableSoapIterator
{
    const DEFAULT_PAGE_SIZE = 100;

    /** @var int */
    protected $currentPage = 1;

    /** @var \stdClass[] Entities buffer got from pageable remote */
    protected $entityBuffer = null;

    abstract protected function applyFilter();

    /**
     * {@inheritdoc}
     */
    protected function findEntitiesToProcess()
    {
        $this->logger->info('Looking for batch');
        $this->entitiesIdsBuffer = $this->getEntityIds();
        $this->currentPage++;

        $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));

        // increment date for further filtering in update mode
        $this->lastSyncDate->add($this->syncRange);

        return empty($this->entitiesIdsBuffer) ? null : true;
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
