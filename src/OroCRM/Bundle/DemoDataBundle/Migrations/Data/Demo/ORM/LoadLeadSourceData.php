<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class LoadLeadSourceData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var array
     */
    protected $data = [
        'Website'     => false,
        'Direct Mail' => false,
        'Affiliate'   => false,
        'Email Marketing'       => false,
        'Outbound'    => false,
        'Partner'     => false
    ];

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
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var ConfigManager $configManager */
        $configManager = $this->container->get('oro_entity_config.config_manager');

        $configFieldModel = $configManager->getConfigFieldModel(
            'OroCRM\Bundle\SalesBundle\Entity\Lead',
            'extend_source'
        );

        $priority = 1;
        foreach ($this->data as $optionSetLabel => $isDefault) {
            $priority++;
            $optionSet = new OptionSet();
            $optionSet
                ->setLabel($optionSetLabel)
                ->setIsDefault($isDefault)
                ->setPriority($priority)
                ->setField($configFieldModel);

            $manager->persist($optionSet);
        }

        $manager->flush();
    }
}
