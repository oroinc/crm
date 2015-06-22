<?php

namespace OroCRM\Bundle\ActivityContactBundle\Bundle\Tests\Unit\DependencyInjection;

use OroCRM\Bundle\ActivityContactBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder       = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);
    }
}
