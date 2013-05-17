<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\JsFormValidationBundle\DependencyInjection\OroJsFormValidationExtension;

class OroJsFormValidationExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $extension = new OroJsFormValidationExtension();

        $configs = array(array());
        $extension->load($configs, $container);

        $this->assertTrue(
            $container->hasDefinition('oro_jsfv.event_listener.load_constraints_listener'),
            'The load constraints listener is loaded'
        );
    }
}
