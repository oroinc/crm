<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
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
        if (is_null($configFieldModel)) {
            // no old options found
            return;
        }

        $enumOptions = [];
        $options     = $this->getLeadSourceOptions($manager, $configFieldModel);
        if (empty($options)) {
            // no old options found
            return;
        }
        $className = ExtendHelper::buildEnumValueClassName('lead_source');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        /** @var OptionSet $option */
        foreach ($options as $option) {
            $enumOption = $enumRepo->createEnumValue(
                $option->getLabel(),
                $option->getPriority(),
                $option->getIsDefault()
            );

            $manager->persist($enumOption);
            $enumOptions[$option->getId()] = $enumOption;
        }

        $qBuilder = $manager->getRepository('OroCRMSalesBundle:Lead')
            ->createQueryBuilder('lead');

        $relRepo   = $manager->getRepository('OroEntityConfigBundle:OptionSetRelation');
        $paginator = new Paginator($qBuilder, false);

        /** @var Lead $lead */
        foreach ($paginator as $lead) {
            /** @var OptionSetRelation $relEntity */
            $relEntity = $relRepo->createQueryBuilder('r')
                ->select('IDENTITY(r.option) as option_id')
                ->where('r.field = ?0', 'r.entity_id = ?1')
                ->getQuery()
                ->execute([$configFieldModel, $lead->getId()]);

            $sourceOptionId = $relEntity[0]['option_id'];
            $source         = empty($enumOptions[$sourceOptionId]) ? null: $enumOptions[$sourceOptionId];

            $lead->setSource($source);
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager    $manager
     * @param FieldConfigModel $fieldModel
     *
     * @return array
     */
    protected function getLeadSourceOptions(ObjectManager $manager, FieldConfigModel $fieldModel)
    {
        $options = $manager->getRepository('OroEntityConfigBundle:OptionSet')
            ->findOptionsByField($fieldModel->getId());

        return $options;
    }
}
