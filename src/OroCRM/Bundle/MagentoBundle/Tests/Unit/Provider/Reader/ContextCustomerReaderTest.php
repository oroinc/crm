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
     * @dataProvider customersDataProvider
     */
    public function testInitializationWithCustomerIds(array $ids, array $expectedIds = [])
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
                    function ($key) use ($ids) {
                        if ($key === 'postProcessCustomerIds') {
                            return $ids;
                        }

                        return false;
                    }
                )
            );

        $this->executionContextMock->expects($this->any())
            ->method('remove')
            ->with($this->equalTo('postProcessCustomerIds'));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @return array
     */
    public function customersDataProvider()
    {
        return [
            'empty' => [[], []],
            'not empty' => [[1, null, '', 2, false, true], [1, 2]]
        ];
    }
}
