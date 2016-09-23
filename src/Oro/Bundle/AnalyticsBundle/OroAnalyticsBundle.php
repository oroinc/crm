<?php

namespace Oro\Bundle\AnalyticsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass\RFMBuilderPass;
use Oro\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass\AnalyticsBuilderPass;

class OroAnalyticsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new AnalyticsBuilderPass())
            ->addCompilerPass(new RFMBuilderPass());
    }
}
