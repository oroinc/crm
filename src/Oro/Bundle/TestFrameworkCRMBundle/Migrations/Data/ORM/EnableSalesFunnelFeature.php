<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\ConfigBundle\Config\GlobalScopeManager;

class UpdateFeaturesConfigs extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configManager = $this->getConfigManager();
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
