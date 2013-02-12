<?php
namespace Oro\Bundle\DataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

/**
 * Abstract job
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractJob implements JobInterface
{

    /**
     * Connector configuration
     *
     * @var ConfigurationInterface
     */
    protected $connectorConfiguration;

    /**
     * Job configuration
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * {@inheritDoc}
     */
    public function configure(ConfigurationInterface $connectorConfig, ConfigurationInterface $jobConfig)
    {
        $this->connectorConfiguration = $connectorConfig;
        $this->configuration          = $jobConfig;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnectorConfiguration()
    {
        return $this->connectorConfiguration;
    }
}
