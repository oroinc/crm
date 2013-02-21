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

    public function initialize(FieldDescriptionInterface $field, $direction = null)
    {
        parent::initialize($field, $direction);

        $flexibleManagerServiceId = $field->getOption('flexible_manager');
        if (!$flexibleManagerServiceId) {
            throw new \LogicException('Flexible entity sorter must have flexible entity manager code.');
        }

        if (!$this->container->has($flexibleManagerServiceId)) {
            throw new \LogicException('There is no flexible entity service ' . $flexibleManagerServiceId . '.');
        }

        $this->flexibleManager = $this->container->get($flexibleManagerServiceId);
    }

    public function apply(ProxyQueryInterface $queryInterface, $direction = null)
    {
        $this->setDirection($direction);

        $alias = $queryInterface->entityJoin($this->getParentAssociationMappings());

        $queryBuilder = $queryInterface->getQueryBuilder();

        /** @var $entityRepository FlexibleEntityRepository */
        $entityRepository = $this->flexibleManager->getFlexibleRepository();
        $entityRepository->applySorterByAttribute($queryBuilder, $alias, $this->getField()->getFieldName(), $direction);
    }
}
