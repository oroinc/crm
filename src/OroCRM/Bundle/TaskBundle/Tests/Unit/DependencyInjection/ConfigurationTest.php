<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;

use OroCRM\Bundle\TaskBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder       = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);
    }

    /**
     * @dataProvider processConfigurationDataProvider
     */
    public function testProcessConfiguration($configs, $expected)
    {
        $configuration = new Configuration();
        $processor     = new Processor();
        $this->assertEquals($expected, $processor->processConfiguration($configuration, $configs));
    }

    public function processConfigurationDataProvider()
    {
        return array(
            'empty' => [
                'configs'  => [[]],
                'expected' => [
                    'my_tasks_in_calendar' => true
                ]
            ],
            'filled' => [
                'configs'  => [
                    [
                        'my_tasks_in_calendar' => false
                    ]
                ],
                'expected' => [
                    'my_tasks_in_calendar' => false
                ]
            ],
        );
    }
}
