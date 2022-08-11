<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Enables sales features.
 */
class EnableSalesFeatures extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $configManager = $this->getConfigManager();

        // Enable Lead and Opportunity features, for tests after upgrade
        $configManager->set('oro_sales.lead_feature_enabled', true);
        $configManager->set('oro_sales.opportunity_feature_enabled', true);

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
