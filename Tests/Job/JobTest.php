<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Job;

use Oro\Bundle\DataFlowBundle\Tests\Job\Demo\MyJob;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MyJob
     */
    protected $job;

    /**
     * Setup
     */
    public function setup()
    {
        $this->job = new MyJob();
    }

    /**
     * Test related method
     */
    public function testConfigure()
    {
        $this->assertNull($this->job->getConfiguration());
        // TODO use basic configuration
    }

    /**
     * Test related method
     */
    public function testRun()
    {
        // TODO : test fail if not configured
        $this->assertTrue($this->job->run());
    }
}
