<?php

namespace OroCRM\Bundle\ChannelBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use OroCRM\Bundle\ChannelBundle\DependencyInjection\CompilerPass\SettingsPass;

class OroCRMChannelBundle extends Bundle
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
