<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

class ConvertLeadSourceData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
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

        $options   = $this->getLeadSourceOptions($manager, $configFieldModel);
        $className = $this->getLeadSourceValueEntityName();

        $enumOptions = [];

        /** @var OptionSet $option */
        foreach ($options as $option) {
            $enumValueId = null;

            /** @var AbstractEnumValue $enumOption */
            $enumOption = new $className(
                $enumValueId,
                $option->getLabel(),
                $option->getPriority(),
                $option->getIsDefault()
            );

            $manager->persist($enumOption);
            $enumOptions[$option->getId()] = $enumOption;
        }

        $qBuilder = $manager->getRepository('OroCRMSalesBundle:Lead')
            ->createQueryBuilder('lead')
            ->setMaxResults(25);

        $relRepo = $manager->getRepository('OroEntityConfigBundle:OptionSetRelation');

        $paginator = new Paginator($qBuilder, false);

        /** @var Lead $lead */
        foreach ($paginator as $lead) {
            /** @var OptionSetRelation $relEntity */
            $relEntity = $relRepo->createQueryBuilder('r')
                ->select('r.option_id')
                ->where('r.field_id = ?0', 'r.entity_id = ?1')
                ->getQuery()
                ->execute([$configFieldModel->getId(), $lead->getId()]);

            $sourceOptionId = $relEntity['option_id'];
            $source         = empty($enumOptions[$sourceOptionId]) ? null: $enumOptions[$sourceOptionId];

            $lead->setSource($source);
        }

        $manager->flush();
    }

    /**
     * @return string
     */
    protected function getLeadSourceValueEntityName()
    {
        return 'Extend\Entity\EV_Lead_Source';
    }

    /**
     * @param ObjectManager $manager
     * @param               $fieldModel
     *
     * @return array
     */
    protected function getLeadSourceOptions(ObjectManager $manager, $fieldModel)
    {
        $options = [];
        try {
            $options = $manager->getRepository('OroEntityConfigBundle:OptionSet')
                ->findOptionsByField($configFieldModel->getId());
        } catch (\Exception $e) {
        }

        return $options;
    }
}
