<?php

namespace Oro\Bundle\GridBundle\Builder\ORM;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Filter\FilterFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;

class DatagridBuilder implements DatagridBuilderInterface
{
    /**
     * @var FilterFactoryInterface
     */
    protected $filterFactory;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var QueryFactoryInterface
     */
    protected $queryFactory;

    /**
     * @param FormFactoryInterface $formFactory
     * @param FilterFactoryInterface $filterFactory
     * @param QueryFactoryInterface $queryFactory
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FilterFactoryInterface $filterFactory,
        QueryFactoryInterface $queryFactory
    ) {
        $this->formFactory     = $formFactory;
        $this->filterFactory   = $filterFactory;
        $this->queryFactory    = $queryFactory;
    }

    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addFilter(
        DatagridInterface $datagrid,
        FieldDescriptionInterface $fieldDescription = null
    ) {
        $filter = $this->filterFactory->create(
            $fieldDescription->getName(),
            $fieldDescription->getType(),
            $fieldDescription->getOptions()
        );
        $datagrid->addFilter($filter);
    }

    /**
     * @param FieldDescriptionCollection $fieldCollection
     * @param array $values
     * @return DatagridInterface
     */
    public function getBaseDatagrid(FieldDescriptionCollection $fieldCollection, array $values = array())
    {
        // TODO: inject pager instance
        /** @var $pager PagerInterface */
        $pager = null;

        $formBuilder = $this->formFactory->createNamedBuilder(
            'filter',
            'form',
            array(),
            array('csrf_protection' => false)
        );

        return new Datagrid(
            $this->queryFactory->createQuery(),
            $fieldCollection,
            $pager,
            $formBuilder,
            $values
        );
    }
}
