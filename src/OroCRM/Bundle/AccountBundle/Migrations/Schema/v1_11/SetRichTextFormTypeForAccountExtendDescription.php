<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class SetRichTextFormTypeForAccountExtendDescription implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->container->get('oro_entity_config.config_manager');

        $formProvider = $configManager->getProvider('form');
        $config = $formProvider->getConfig('OroCRM\Bundle\AccountBundle\Entity\Account', 'extend_description');

        $config->set('type', 'oro_resizeable_rich_text');

        $configManager->persist($config);
        $configManager->flush();
    }
}
