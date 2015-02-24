<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

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

        if (!$iterator instanceof UpdatedLoaderInterface) {
            return $iterator;
        }

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
        $orders = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS);

        $entitiesIdsBuffer = array_map(
            function (Order $order) {
                if (!$order->getCustomer()) {
                    return false;
                }

                $customer = $order->getCustomer();

                if (!$customer->getOriginId()) {
                    return false;
                }

                return $customer->getOriginId();
            },
            $orders
        );

        return array_unique(array_filter($entitiesIdsBuffer));
    }
}
