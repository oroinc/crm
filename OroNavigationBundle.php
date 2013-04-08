<?php

namespace Oro\Bundle\NavigationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Oro\Bundle\NavigationBundle\DependencyInjection\Compiler\MenuBuilderChainPass;
use Oro\Bundle\NavigationBundle\DependencyInjection\Security\Factory\ApiFactory;

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
