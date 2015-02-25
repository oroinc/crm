<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\OrderDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\OrderConnector;

class OrderInfoReader extends OrderConnector
{
    /** @var string */
    protected $orderClassName;

    /** @var bool[] */
    protected $loaded = [];

    /**
     * @param string $orderClassName
     */
    public function setOrderClassName($orderClassName)
    {
        $this->orderClassName = $orderClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $incrementId = $this->getOrder()->getIncrementId();

        if (!empty($this->loaded[$incrementId])) {
            return null;
        }

        $order = $this->transport->getOrderInfo($incrementId);

        $this->loaded[$incrementId] = true;

        OrderDependencyManager::addDependencyData($order, $this->transport->getDependencies());

        return ConverterUtils::objectToArray($order);
    }

    /**
     * @return Order
     */
    protected function getOrder()
    {
        $configuration = $this->getContext()->getConfiguration();

        if (empty($configuration['data'])) {
            throw new \InvalidArgumentException('Data is missing');
        }

        $order = $configuration['data'];

        if (!$order instanceof Order) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Instance of "%s" expected, "%s" given.',
                    $this->orderClassName,
                    is_object($order) ? get_class($order) : gettype($order)
                )
            );
        }

        return $order;
    }
}
