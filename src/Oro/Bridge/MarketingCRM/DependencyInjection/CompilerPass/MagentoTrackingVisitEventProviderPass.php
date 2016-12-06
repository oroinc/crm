<?php

namespace Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bridge\MarketingCRM\Provider\TrackingVisitEventProvider;

class MagentoTrackingVisitEventProviderPass implements CompilerPassInterface
{
    const PROVIDER_ID = 'oro_magento.provider.tracking_visit_event';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $magentoProvider = $container->getDefinition(self::PROVIDER_ID);
        $magentoProvider->setClass(TrackingVisitEventProvider::class);

        $magentoProvider->addMethodCall('addFeature', ['tracking']);
        $checkerReference = new Reference('oro_featuretoggle.checker.feature_checker');
        $magentoProvider->addMethodCall('setFeatureChecker', [$checkerReference]);
    }
}
