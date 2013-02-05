<?php
namespace Oro\Bundle\DataFlowBundle\DependencyInjection\Chain;

use Oro\Bundle\DataFlowBundle\Connector\ConnectorInterface;

/**
 * Chain to define all connectors
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
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

    /**
     * Add a connector
     * @param ConnectorInterface $connector
     *
     * @return ConnectorChain
     */
    public function addConnector(ConnectorInterface $connector)
    {
        $this->connectors[] = $connector;

        return $this;
    }

    /**
     * Get the list of connectors
     *
     * @return multitype:ConnectorInterface
     */
    public function getConnectors()
    {
        return $this->connectors;
    }

}
