<?php
namespace Oro\Bundle\DataFlowBundle\Connector\Job;

use Oro\Bundle\DataFlowBundle\Connector\Job\JobInterface;

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
     * @var string
     */
    protected $code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get job code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

}
