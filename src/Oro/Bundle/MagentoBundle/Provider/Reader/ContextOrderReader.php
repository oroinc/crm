<?php

namespace Oro\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\MagentoBundle\Provider\Connector\OrderConnector;
use Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class ContextOrderReader extends OrderConnector
{
    const CONTEXT_POST_PROCESS_ORDERS = 'postProcessContextOrderIds';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $iterator = parent::getConnectorSource();

        if (!$iterator instanceof UpdatedLoaderInterface) {
            return $iterator;
        }

        $orderIds = $this->getOrderIds();

        $iterator->setEntitiesIdsBuffer($orderIds);

        return $iterator;
    }

    /**
     * @return array
     */
    public function getOrderIds()
    {
        $ids = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(self::CONTEXT_POST_PROCESS_ORDERS);

        $ids = array_unique(array_filter($ids));

        sort($ids);

        return $ids;
    }
}
