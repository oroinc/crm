<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\MagentoBundle\Provider\Connector\CustomerConnector;
use Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

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

        $customerIds = $this->getCustomerIds();

        $iterator->setEntitiesIdsBuffer($customerIds);

        return $iterator;
    }

    /**
     * @return array
     */
    public function getCustomerIds()
    {
        $ids = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(self::CONTEXT_POST_PROCESS_CUSTOMERS);

        $ids = array_unique(array_filter($ids));

        sort($ids);

        return $ids;
    }
}
