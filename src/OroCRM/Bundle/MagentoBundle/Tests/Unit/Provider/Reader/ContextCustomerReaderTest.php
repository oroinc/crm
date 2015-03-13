<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
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
        $reader = new ContextCustomerReader($contextRegistry, $logger, $contextMediator, $this->config);

        $reader->setContextKey('postProcessOrders');

        return $reader;
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Context key is missing
     */
    public function testFailedWithoutExecutionContext()
    {
        /** @var ContextCustomerReader $connector */
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);
        $connector->setContextKey(null);

        $iterator = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface');
        $iterator->expects($this->never())->method('setEntitiesIdsBuffer');

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    public function testInitializationWithNotMatchedIterator()
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $iterator = $this->getMock('\Iterator');
        $iterator->expects($this->never())->method('setEntitiesIdsBuffer');

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
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

        $iterator = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface');

        $iterator->expects($this->once())
            ->method('setEntitiesIdsBuffer')
            ->with($expectedIds);

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
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
            'order with customer without originId' => [[$this->getOrder([])]],
            'nullable originId' => [[$this->getOrder([], false)]],
            'invalid originId' => [[$this->getOrder([], 0)]],
            'valid order' => [[$this->getOrder([], $originId)], [$originId]],
            'multiple orders' => [
                [
                    $this->getOrder([], $originId),
                    $this->getOrder([], $originId2)
                ],
                [$originId, $originId2]
            ],
            'duplicate customer ids' => [
                [
                    $this->getOrder([], $originId),
                    $this->getOrder([], $originId)
                ],
                [$originId]
            ]
        ];
    }

    /**
     * @param mixed $customerData
     * @param mixed $originId
     * @return array
     */
    protected function getOrder($customerData = null, $originId = null)
    {
        $order = [];

        if (null !== $customerData) {
            if ($originId) {
                $customerData['originId'] = $originId;
            }

            $order['customer'] = $customerData;
        }

        return $order;
    }
}
