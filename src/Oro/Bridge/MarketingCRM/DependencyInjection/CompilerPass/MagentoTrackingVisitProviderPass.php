<?php

namespace Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bridge\MarketingCRM\Provider\TrackingVisitProvider;

class MagentoTrackingVisitProviderPass implements CompilerPassInterface
{
    const PROVIDER_ID = 'oro_magento.provider.tracking_visit';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $magentoProvider = $container->getDefinition(self::PROVIDER_ID);
        $magentoProvider->setClass(TrackingVisitProvider::class);
    }
}
