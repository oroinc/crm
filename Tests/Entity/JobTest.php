<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Entity;

use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Entity\Job;

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
     * @var Job
     */
    protected $job;

    /**
     * Setup
     */
    public function setup()
    {
        $this->job = new Job();
    }

    /**
     * Test related method
     */
    public function testGettersSetters()
    {
        $this->assertNull($this->job->getId());
        $this->assertNull($this->job->getConnectorService());
        $this->assertNull($this->job->getConnectorConfiguration());
        $this->assertNull($this->job->getJobService());
        $this->assertNull($this->job->getJobConfiguration());

        $configurationCon = new Configuration();
        $configurationJob = new Configuration();
        $this->job->setConnectorService('my.connector.id');
        $this->job->setJobService('my.job.id');
        $this->job->setConnectorConfiguration($configurationCon);
        $this->job->setJobConfiguration($configurationJob);

        $this->assertEquals($this->job->getConnectorService(), 'my.connector.id');
        $this->assertEquals($this->job->getConnectorConfiguration(), $configurationCon);
        $this->assertEquals($this->job->getJobService(), 'my.job.id');
        $this->assertEquals($this->job->getJobConfiguration(), $configurationJob);
    }
}
