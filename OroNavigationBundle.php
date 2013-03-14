<?php

namespace Oro\Bundle\NavigationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\MenuBuilderChainPass;

use Oro\Bundle\NavigationBundle\DependencyInjection\Security\Factory\ApiFactory;
use Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\ItemBuilderChainPass;

class OroNavigationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ApiFactory());

        $container->addCompilerPass(new MenuBuilderChainPass());
    }
}
