<?php
namespace Oro\Bundle\DataFlowBundle\Connector\Job;

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
     * Get gode
     */
    public function getCode();

    /**
     * Process the job
     */
    public function process();

}
