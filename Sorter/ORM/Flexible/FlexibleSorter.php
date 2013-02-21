<?php

namespace Oro\Bundle\GridBundle\Sorter\ORM\Flexible;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\GridBundle\Sorter\ORM\Sorter;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class FlexibleSorter extends Sorter
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FieldDescriptionInterface $field
     * @param string $direction
     * @throws \LogicException
     */
    public function initialize(FieldDescriptionInterface $field, $direction = null)
    {
        parent::initialize($field, $direction);

        $flexibleEntityName = $field->getOption('flexible_name');
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity filter must have flexible entity name.');
        }

        $this->flexibleManager = $this->getFlexibleManager($flexibleEntityName);
    }

    /**
     * @param ProxyQueryInterface $queryInterface
     * @param string $direction
     */
    public function apply(ProxyQueryInterface $queryInterface, $direction = null)
    {
        $this->setDirection($direction);

        $alias = $queryInterface->entityJoin($this->getParentAssociationMappings());

        $queryBuilder = $queryInterface->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->flexibleManager->getFlexibleRepository();
        $entityRepository->applySorterByAttribute($queryBuilder, $alias, $this->getField()->getFieldName(), $direction);
    }

    /**
     * @param string $flexibleEntityName
     * @return FlexibleManager
     * @throws \LogicException
     */
    protected function getFlexibleManager($flexibleEntityName)
    {
        $flexibleConfig = $this->container->getParameter('oro_flexibleentity.flexible_config');

        // validate configuration
        if (!isset($flexibleConfig['entities_config'][$flexibleEntityName])
            || !isset($flexibleConfig['entities_config'][$flexibleEntityName]['flexible_manager'])
        ) {
            throw new \LogicException(
                'There is no flexible manager configuration for entity ' . $flexibleEntityName . '.'
            );
        }

        $flexibleManagerServiceId = $flexibleConfig['entities_config'][$flexibleEntityName]['flexible_manager'];
        return $this->container->get($flexibleManagerServiceId);
    }
}
