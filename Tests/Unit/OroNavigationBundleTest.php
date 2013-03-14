<?php
namespace Oro\Bundle\NavigationBundle\Tests\Unit;

use Oro\Bundle\NavigationBundle\OroNavigationBundle;

class OroNavigationBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\MenuBuilderChainPass'));

        $security = $this->getMockBuilder('Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension')
            ->disableOriginalConstructor()
            ->getMock();

        $security->expects($this->once())
            ->method('addSecurityListenerFactory')
            ->with($this->isInstanceOf('Oro\Bundle\NavigationBundle\DependencyInjection\Security\Factory\ApiFactory'));

        $container->expects($this->once())
            ->method('getExtension')
            ->with('security')
            ->will($this->returnValue($security));

        $bundle = new OroNavigationBundle();
        $bundle->build($container);
    }
}
