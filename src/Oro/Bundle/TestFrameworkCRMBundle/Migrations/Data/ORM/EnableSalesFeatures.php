<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;

class EnableSalesFeatures extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configManager = $this->getConfigManager();

        // Enable Lead and Opportunity features, for tests after upgrade
        $configManager->set('oro_sales.lead_feature_enabled', true);
        $configManager->set('oro_sales.opportunity_feature_enabled', true);

        // Enable Sales Funnel feature for REST API tests
        $configManager->set('oro_sales.salesfunnel_feature_enabled', true);

        $configManager->flush();
    }

    /**
     * @return GlobalScopeManager
     */
    protected function getConfigManager()
    {
        return $this->container->get('oro_config.global');
    }
}
