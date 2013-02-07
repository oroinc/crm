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
