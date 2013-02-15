<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Entity;

use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Entity\Connector;

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
     * @var Connector
     */
    protected $connector;

    /**
     * Setup
     */
    public function setup()
    {
        $this->connector = new Connector();
    }

    /**
     * Test related method
     */
    public function testGettersSetters()
    {
        $this->assertNull($this->connector->getId());
        $this->assertNull($this->connector->getConnectorService());
        $this->assertNull($this->connector->getConnectorConfiguration());

        $this->connector->setConnectorService('my.connector.id');
        $configuration = new Configuration();

        $this->connector->setConnectorConfiguration($configuration);
        $this->assertEquals($this->connector->getConnectorService(), 'my.connector.id');
        $this->assertEquals($this->connector->getConnectorConfiguration(), $configuration);
    }
}
