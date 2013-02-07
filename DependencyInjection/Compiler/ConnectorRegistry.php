<?php
namespace Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler;

use Oro\Bundle\DataFlowBundle\Connector\Job\JobInterface;
use Oro\Bundle\DataFlowBundle\Connector\ConnectorInterface;

/**
 * Aims to register all connectors
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConnectorRegistry
{

    /**
     *
     * @var \ArrayAccess
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
     * Add a job to a connector
     *
     * @param ConnectorInterface $connector the connector
     * @param JobInterface       $job       the job
     *
     *
     * @return ConnectorRegistry
     */
    public function addToConnector(ConnectorInterface $connector, JobInterface $job)
    {
        $connector->addJob($job);
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
