<?php

namespace Oro\Bundle\ChannelBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ChannelBundle\DependencyInjection\CompilerPass\SettingsPass;

class OroChannelBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SettingsPass());
    }
}
