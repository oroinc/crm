<?php

namespace Oro\Bundle\AddressBundle;

use Oro\Bundle\AddressBundle\DependencyInjection\Compiler\AddressProviderPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Oro\Bundle\NavigationBundle\DependencyInjection\Security\Factory\ApiFactory;

class OroAddressBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');
        // $extension->addSecurityListenerFactory(new ApiFactory());

        $container->addCompilerPass(new AddressProviderPass());
    }
}
