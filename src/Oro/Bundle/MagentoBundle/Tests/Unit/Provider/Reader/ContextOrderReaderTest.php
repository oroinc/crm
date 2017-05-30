<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextOrderReader;
use Oro\Bundle\MagentoBundle\Tests\Unit\Provider\MagentoConnectorTestCase;

class ContextOrderReaderTest extends MagentoConnectorTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    ) {
        return new ContextOrderReader($contextRegistry, $logger, $contextMediator, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIteratorGetterMethodName()
    {
        return 'getOrders';
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

        $iterator = $this
            ->getMockForAbstractClass(
                '\Iterator',
                [],
                '',
                true,
                true,
                true,
                ['setEntitiesIdsBuffer']
            );
        $iterator->expects($this->never())->method('setEntitiesIdsBuffer');

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @param array $ids
     * @param array $expectedIds
     *
     * @dataProvider orderDataProvider
     */
    public function testInitializationWithCustomerIds(array $ids, array $expectedIds = [])
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $iterator = $this->createMock('Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface');

        $iterator->expects($this->once())
            ->method('setEntitiesIdsBuffer')
            ->with($expectedIds);

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $this->executionContextMock->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($ids) {
                        if ($key === ContextOrderReader::CONTEXT_POST_PROCESS_ORDERS) {
                            return $ids;
                        }

                        return false;
                    }
                )
            );

        $this->executionContextMock->expects($this->any())
            ->method('remove')
            ->with($this->equalTo(ContextOrderReader::CONTEXT_POST_PROCESS_ORDERS));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @return array
     */
    public function orderDataProvider()
    {
        return [
            'empty' => [[], []],
            'not empty' => [[1, null, '', 2, false, true], [1, 2]]
        ];
    }
}
