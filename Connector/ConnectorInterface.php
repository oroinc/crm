<?php
namespace Oro\Bundle\DataFlowBundle\Connector;

use Oro\Bundle\DataFlowBundle\Connector\JobInterface;

/**
 * Connector interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface ConnectorInterface
{

    /**
     * Get jobs
     *
     * @return \ArrayAccess
     */
    public function getJobs();

    /**
     * Get jobs
     *
     * @param string $jobCode
     *
     * @return JobInterface
     */
    public function getJob($jobCode);

    /**
     * Add a job
     *
     * @param JobInterface $job the job
     */
    public function addJob(JobInterface $job);

    /**
     * Process a job
     *
     * @param string $jobCode the job code
     */
    public function process($jobCode);

}
