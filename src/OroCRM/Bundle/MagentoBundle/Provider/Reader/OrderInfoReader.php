<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\OrderDependencyManager;

class OrderInfoReader extends AbstractInfoReader
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        /** @var Order $order */
        $order = $this->getData();
        $incrementId = $order->getIncrementId();

        if (!$incrementId || !empty($this->loaded[$incrementId])) {
            return null;
        }

        $result = $this->transport->getOrderInfo($incrementId);

        $this->loaded[$incrementId] = true;

        OrderDependencyManager::addDependencyData($result, $this->transport);

        return ConverterUtils::objectToArray($result);
    }
}
