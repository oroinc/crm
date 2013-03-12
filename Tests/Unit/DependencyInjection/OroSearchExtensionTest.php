<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\SearchBundle\DependencyInjection\OroSearchExtension;

class OroSearchExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testGetAlias()
    {
        $searchExtension = new OroSearchExtension(array(), $this->container);
        $this->assertEquals('oro_search', $searchExtension->getAlias());
    }
}
