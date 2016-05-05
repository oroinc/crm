<?php

namespace OroCRM\Bundle\SalesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ForecastOfOpportunitiesWidgetPass implements CompilerPassInterface
{
    const PROVIDER_SERVICE_ID = 'oro_dashboard.widget_config_value.widget_business_unit_select.converter';
    const OPPORTUNITY_CLASS = 'orocrm_sales.opportunity.class';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_SERVICE_ID) ||
            !$container->hasParameter(self::OPPORTUNITY_CLASS)) {
            return;
        }

        $serviceDef = $container->getDefinition(self::PROVIDER_SERVICE_ID);
        $serviceDef->addMethodCall(
            'addAclByEntityPermission',
            [
                $container->getParameter(self::OPPORTUNITY_CLASS),
                'VIEW'
            ]
        );
    }
}
