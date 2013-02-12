<?php
namespace Oro\Bundle\DataFlowBundle\Job;

use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

/**
 * Job interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface JobInterface
{

    /**
     * Configure
     * @param ConfigurationInterface $connectorConfig
     * @param ConfigurationInterface $jobConfig
     *
     * @return JobInterface
     */
    public function configure(ConfigurationInterface $connectorConfig, ConfigurationInterface $jobConfig);

    /**
     * Get configuration
     *
     * @return ConfigurationInterface
     */
    public function getConnectorConfiguration();

    /**
     * Get configuration
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration();

    /**
     * Run the job
     */
    public function run();
}
