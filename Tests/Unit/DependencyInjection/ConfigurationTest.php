<?php
namespace Oro\Bundle\SearchBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\SearchBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $this->assertTrue($configuration->getConfigTreeBuilder() instanceof TreeBuilder);
    }
}
