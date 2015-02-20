<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;

class ContextCustomerReader extends CustomerConnector
{
    /** @var Order[] */
    protected $orders = [];

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $iterator = parent::getConnectorSource();

        $customerIds = $this->getCustomerIds();
        if ($customerIds) {
            $iterator->setEntitiesIdsBuffer($customerIds);
        }

        return $iterator;
    }

    /**
     * @return array
     */
    public function getCustomerIds()
    {
        $orders = $this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS) ?: [];

        $entitiesIdsBuffer = array_map(
            function (Order $order) {
                return $order->getCustomer()->getOriginId();
            },
            $orders
        );

        return $entitiesIdsBuffer;
    }
}
