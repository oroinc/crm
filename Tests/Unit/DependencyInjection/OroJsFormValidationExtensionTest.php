<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\JsFormValidationBundle\DependencyInjection\OroJsFormValidationExtension;

class OroJsFormValidationExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->never())->method($this->anything());

        $gridExtension = new OroJsFormValidationExtension();
        $configs = array();
        $gridExtension->load($configs, $container);
    }
}
