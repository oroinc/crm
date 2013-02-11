<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Connector;

use Oro\Bundle\DataFlowBundle\Tests\Connector\Demo\MyConnector;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MyConnector
     */
    protected $connector;

    /**
     * Setup
     */
    public function setup()
    {
        $this->connector = new MyConnector();
    }

    /**
     * Test related method
     */
    public function testConfigure()
    {
        $this->assertNull($this->connector->getConfiguration());
        // TODO use basic configuration
    }
}
