<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Connector\Job\JobInterface;
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
     * @var \ArrayAccess
     */
    protected $jobs;

    /**
     * Connectors
     */
    public function __construct()
    {
        $this->jobs = array();
    }

    /**
     * Get jobs
     *
     * @return \ArrayAccess
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Get job by code
     *
     * @param string $code
     *
     * @return JobInterface
     */
    public function getJob($code)
    {
        var_dump(array_keys($this->jobs)); exit();

        if (!isset($this->jobs[$code])) {
            throw new ConfigurationException('job '.$code.' is unknown');
        }

        return $this->jobs[$code];
    }

    /**
     * Add a job
     * @param JobInterface $job
     */
    public function addJob(JobInterface $job)
    {
        $this->jobs[$job->getCode()]= $job;
    }

}
