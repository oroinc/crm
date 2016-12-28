<?php

namespace Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bridge\MarketingCRM\Provider\WebsiteVisitProvider;

class MagentoWebsiteVisitProviderPass implements CompilerPassInterface
{
    const PROVIDER_ID = 'oro_magento.provider.website_visit';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $magentoProvider = $container->getDefinition(self::PROVIDER_ID);
        $magentoProvider->setClass(WebsiteVisitProvider::class);

        $magentoProvider->addMethodCall('addFeature', ['tracking']);
        $checkerReference = new Reference('oro_featuretoggle.checker.feature_checker');
        $magentoProvider->addMethodCall('setFeatureChecker', [$checkerReference]);
    }
}
