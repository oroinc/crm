<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Twig;

use OroCRM\Bundle\CaseBundle\Twig\CaseExtension;

class CaseExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var CaseExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new CaseExtension($this->manager);
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);

        $expectedFunctions = array(
            'get_view_route',
        );

        /** @var \Twig_SimpleFunction $function */
        foreach ($functions as $function) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
            $this->assertContains($function->getName(), $expectedFunctions);
        }
    }

    public function testGetName()
    {
        $this->assertEquals(CaseExtension::NAME, $this->extension->getName());
    }

    public function testGetViewRoute()
    {
        $expectedClass = 'test class';
        $expectedRoute = 'oro_route';

        $metadata = new \StdClass();
        $metadata->routeView = $expectedRoute;
        $this->manager->expects($this->once())
            ->method('getEntityMetadata')
            ->with($expectedClass)
            ->will($this->returnValue($metadata));

        $actual = $this->extension->getViewRoute($expectedClass);
        $this->assertEquals($expectedRoute, $actual);
    }
}
