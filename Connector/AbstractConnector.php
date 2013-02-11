<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Job\JobInterface;
use Oro\Bundle\DataFlowBundle\Exception\ConfigurationException;

/**
 * Abstract connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractConnector implements ConnectorInterface
{

    /**
     * Job configuration
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * {@inheritDoc}
     */
    public function configure(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration->process();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
