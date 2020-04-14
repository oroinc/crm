<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Connector;

use Oro\Bundle\MagentoBundle\Provider\Connector\AbstractMagentoConnector;
use Oro\Bundle\MagentoBundle\Provider\Connector\InitialConnectorInterface;
use Oro\Bundle\MagentoBundle\Tests\Unit\Provider\MagentoConnectorTestCase;

abstract class InitialConnectorTestCase extends MagentoConnectorTestCase
{
    public function testInitialInterface()
    {
        $this->assertInstanceOf(
            'Oro\Bundle\MagentoBundle\Provider\Connector\InitialConnectorInterface',
            $this->getConnector($this->transportMock, $this->stepExecutionMock)
        );
    }

    public function testGetImportJobName()
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $this->assertIsString($connector->getImportJobName());
    }

    public function testGetLabel()
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $this->assertIsString($connector->getLabel());
    }

    public function testGetImportEntityFQCNFailed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity FQCN is missing');

        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $connector->getImportEntityFQCN();
    }

    public function testGetImportEntityFQCN()
    {
        /** @var InitialConnectorInterface|AbstractMagentoConnector $connector */
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $connector->setClassName('\stdClass');

        $this->assertIsString($connector->getImportEntityFQCN());
    }
}
