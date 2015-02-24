<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class ContextCustomerReader extends CustomerConnector
{
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
        $orders = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS);

        $entitiesIdsBuffer = array_map(
            function (array $order) {
                if (empty($order['customer']['originId'])) {
                    return false;
                }

                return $order['customer']['originId'];
            },
            $orders
        );

        return array_unique(array_filter($entitiesIdsBuffer));
    }
}
