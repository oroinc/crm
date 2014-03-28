<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

/**
 * TODO: Remove this class after release in scope of https://magecore.atlassian.net/browse/BAP-3605
 */
class RemoveAccountExtendFields extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var string
     */
    protected $entity = 'OroCRM\Bundle\AccountBundle\Entity\Account';

    /**
     * @var array
     */
    protected $fields = array('extend_phone', 'extend_email', 'extend_fax');

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->container->get('oro_entity_config.config_manager');

        $hasFields = false;

        foreach ($this->fields as $fieldName) {
            if (!$configManager->hasConfig($this->entity, $fieldName)) {
                continue;
            }

            $extendFieldConfig = $configManager->getProvider('extend')->getConfig($this->entity, $fieldName);
            $extendFieldConfig->set('state', ExtendScope::STATE_DELETED);
            $configManager->persist($extendFieldConfig);

            $formFieldConfig = $configManager->getProvider('form')->getConfig($this->entity, $fieldName);
            $formFieldConfig->set('is_enabled', false);
            $configManager->persist($formFieldConfig);

            $viewFieldConfig = $configManager->getProvider('view')->getConfig($this->entity, $fieldName);
            $viewFieldConfig->set('is_displayable', false);
            $configManager->persist($viewFieldConfig);

            $hasFields = true;
        }

        if ($hasFields) {
            $entityConfig = $configManager->getProvider('extend')->getConfig($this->entity);
            $entityConfig->set('upgradeable', true);

            $configManager->persist($entityConfig);
            $configManager->flush();
        }
    }
}
