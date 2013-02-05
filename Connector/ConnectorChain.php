<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

class ConnectorChain
{

    /**
     *
     * @var multitype
     */
    protected $connectors;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connectors = array();
    }

    public function addConnector(ConnectorInterface $connector)
    {
        $this->connectors[] = $connector;
    }

    public function getConnector($alias)
    {
        return $this->connectors[$alias];
    }

    public function getConnectors()
    {
        return $this->connectors;
    }

}
