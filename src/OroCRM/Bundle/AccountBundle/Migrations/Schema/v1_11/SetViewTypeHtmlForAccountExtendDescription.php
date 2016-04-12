<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class SetViewTypeHtmlForAccountExtendDescription implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->container->get('oro_entity_config.config_manager');

        $formProvider = $configManager->getProvider('view');
        $config = $formProvider->getConfig('OroCRM\Bundle\AccountBundle\Entity\Account', 'extend_description');

        $config->set('type', 'html');

        $configManager->persist($config);
        $configManager->flush();
    }
}
