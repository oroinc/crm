<?php
namespace Oro\Bundle\DataFlowBundle\Connector\Job;

use Oro\Bundle\DataFlowBundle\Connector\AbstractConfiguration;

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
     * Configure
     * @param \ArrayAccess
     */
    public function configure($parameters);

    /**
     * Run the job
     */
    public function run();

}
