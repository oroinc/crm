<?php

namespace Oro\Bridge\MarketingCRM;

use Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass\MagentoTrackingVisitEventProviderPass;
use Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass\MagentoTrackingVisitProviderPass;
use Oro\Bridge\MarketingCRM\DependencyInjection\CompilerPass\MagentoWebsiteVisitProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroMarketingCRMBridgeBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MagentoTrackingVisitProviderPass());
        $container->addCompilerPass(new MagentoTrackingVisitEventProviderPass());
        $container->addCompilerPass(new MagentoWebsiteVisitProviderPass());
    }
}
