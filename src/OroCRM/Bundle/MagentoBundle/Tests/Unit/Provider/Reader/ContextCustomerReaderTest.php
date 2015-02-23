<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\MagentoConnectorTestCase;

class ContextCustomerReaderTest extends MagentoConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new ContextCustomerReader($contextRegistry, $logger, $contextMediator, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getCustomers';
    }

    /**
     * {@inheritdoc}
     */
    protected function supportsForceMode()
    {
        return true;
    }

    public function testInitializationWithNotMatchedIterator()
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);
        $this->transportMock->expects($this->once())->method('init');

        $iterator = $this->getMock('\Iterator');
        $iterator->expects($this->never())->method('setEntitiesIdsBuffer');

        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @param array $orders
     * @param array $expectedIds
     *
     * @dataProvider customersDataProvider
     */
    public function testInitializationWithCustomerIds(array $orders = [], array $expectedIds = [])
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);
        $this->transportMock->expects($this->once())->method('init');

        $iterator = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface');

        $iterator->expects($expectedIds ? $this->once() : $this->never())
            ->method('setEntitiesIdsBuffer')
            ->with($expectedIds);

        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $this->executionContextMock->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($orders) {
                        if ($key === 'postProcessOrders') {
                            return $orders;
                        }

                        return false;
                    }
                )
            );

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @return array
     */
    public function customersDataProvider()
    {
        $originId = uniqid();
        $originId2 = uniqid();

        return [
            'empty orders' => [],
            'order without customer' => [[$this->getOrder()]],
            'order with customer without originId' => [[$this->getOrder(new Customer())]],
            'nullable originId' => [[$this->getOrder(new Customer(), false)]],
            'invalid originId' => [[$this->getOrder(new Customer(), 0)]],
            'valid order' => [[$this->getOrder(new Customer(), $originId)], [$originId]],
            'multiple orders' => [
                [
                    $this->getOrder(new Customer(), $originId),
                    $this->getOrder(new Customer(), $originId2)
                ],
                [$originId, $originId2]
            ],
            'duplicate customer ids' => [
                [
                    $this->getOrder(new Customer(), $originId),
                    $this->getOrder(new Customer(), $originId)
                ],
                [$originId]
            ],
        ];
    }

    /**
     * @param Customer|null $customer
     * @param mixed $originId
     * @return Order
     */
    protected function getOrder($customer = null, $originId = null)
    {
        $order = new Order();

        if ($customer) {
            if ($originId) {
                $customer->setOriginId($originId);
            }

            $order->setCustomer($customer);
        }

        return $order;
    }
}
