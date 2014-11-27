<?php

namespace OroCRM\Bundle\AnalyticsBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use OroCRM\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass\RFMBuilderPass;
use OroCRM\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass\AnalyticsBuilderPass;

class OroCRMAnalyticsBundle extends Bundle
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
