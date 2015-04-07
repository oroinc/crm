<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class ContextCustomerReader extends CustomerConnector
{
    const CONTEXT_POST_PROCESS_CUSTOMERS = 'postProcessCustomerIds';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $iterator = parent::getConnectorSource();

        if (!$iterator instanceof UpdatedLoaderInterface) {
            return $iterator;
        }

        $iterator->setEntitiesIdsBuffer($this->getCustomerIds());

        return $iterator;
    }

    /**
     * @return array
     */
    public function getCustomerIds()
    {
        $ids = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(self::CONTEXT_POST_PROCESS_CUSTOMERS);

        $this->stepExecution->getJobExecution()->getExecutionContext()->remove(self::CONTEXT_POST_PROCESS_CUSTOMERS);

        sort($ids);

        return array_unique(array_filter($ids));
    }
}
