<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Oro\Bundle\FilterBundle\DependencyInjection\OroFilterExtension;

class OroGridExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Service IDs to ensure that all required files were loaded
     *
     * @var array
     */
    protected $expectedDefinitions = array(
        'oro_filter.form.type.date_range',
        'oro_filter.form.type.filter.datetime_range'
    );

    /**
     * @var array
     */
    protected $actualDefinitions = array();

    public function testLoad()
    {
        $container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('setDefinition')
        );
        $container->expects($this->any())
            ->method('setDefinition')
            ->will($this->returnCallback(array($this, 'setDefinitionCallback')));

        $extension = new OroFilterExtension();
        $extension->load(array(), $container);

        foreach ($this->expectedDefinitions as $serviceId) {
            $this->assertArrayHasKey($serviceId, $this->actualDefinitions);
        }
    }

    /**
     * Callback for ContainerBuilder::setDefinition
     *
     * @param string $id
     * @param Definition $definition
     */
    public function setDefinitionCallback($id, Definition $definition)
    {
        $this->actualDefinitions[$id] = $definition;
    }
}
