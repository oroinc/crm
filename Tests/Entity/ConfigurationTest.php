<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Entity;

use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Tests\Configuration\Demo\MyConfiguration;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * Setup
     */
    public function setup()
    {
        $this->configuration = new Configuration();
    }

    /**
     * Test related method
     */
    public function testGettersSetters()
    {
        $this->assertNull($this->configuration->getId());
        $this->assertNull($this->configuration->getTypeName());
        $this->assertEquals($this->configuration->getFormat(), 'json');
        $this->assertEmpty($this->configuration->getData());

        $this->configuration->setTypeName('my type');
        $this->configuration->setFormat('xml');
        $this->configuration->setData('test:text');

        $this->assertEquals($this->configuration->getTypeName(), 'my type');
        $this->assertEquals($this->configuration->getFormat(), 'xml');
        $this->assertEquals($this->configuration->getData(), 'test:text');
    }
}
