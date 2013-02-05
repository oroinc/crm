<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

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

    /**
     * Process a job
     * @param string $code
     */
    public function process($code)
    {
        $job = $this->getJob($code);
        $job->process();
    }
}
