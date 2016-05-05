<?php

namespace OroCRM\Bundle\SalesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use OroCRM\Bundle\SalesBundle\DependencyInjection\Compiler\ForecastOfOpportunitiesWidgetPass;

class OroCRMSalesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ForecastOfOpportunitiesWidgetPass());
    }
}
