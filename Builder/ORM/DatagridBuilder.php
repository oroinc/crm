<?php

namespace Oro\Bundle\GridBundle\Builder\ORM;

use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\Pager;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Filter\FilterFactoryInterface;
use Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface;
use Oro\Bundle\GridBundle\Action\ActionFactoryInterface;
use Oro\Bundle\GridBundle\Property\PropertyInterface;

class DatagridBuilder implements DatagridBuilderInterface
{
    /**
     * @var FilterFactoryInterface
     */
    protected $filterFactory;

    /**
     * @var SorterFactoryInterface
     */
    protected $sorterFactory;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var ActionFactoryInterface
     */
    protected $actionFactory;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param FormFactoryInterface $formFactory
     * @param FilterFactoryInterface $filterFactory
     * @param SorterFactoryInterface $sorterFactory
     * @param ActionFactoryInterface $actionFactory
     * @param string $className
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        FilterFactoryInterface $filterFactory,
        SorterFactoryInterface $sorterFactory,
        ActionFactoryInterface $actionFactory,
        $className
    ) {
        $this->formFactory   = $formFactory;
        $this->filterFactory = $filterFactory;
        $this->sorterFactory = $sorterFactory;
        $this->actionFactory = $actionFactory;
        $this->className     = $className;
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
            $fieldDescription->getOption('filter_type'),
            $fieldDescription->getOptions()
        );
        $filter->setOption('data_type', $fieldDescription->getType());
        $datagrid->addFilter($filter);
    }

    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $field
     */
    public function addSorter(DatagridInterface $datagrid, FieldDescriptionInterface $field)
    {
        $sorter = $this->sorterFactory->create($field);

        $datagrid->addSorter($sorter);
    }

    /**
     * @param DatagridInterface $datagrid
     * @param array $parameters
     */
    public function addRowAction(DatagridInterface $datagrid, array $parameters)
    {
        $action = $this->actionFactory->create(
            $parameters['name'],
            $parameters['type'],
            isset($parameters['acl_resource']) ? $parameters['acl_resource'] : null,
            isset($parameters['options']) ? $parameters['options'] : array()
        );

        if ($action->isGranted()) {
            $datagrid->addRowAction($action);
        }
    }

    /**
     * Add property to datagrid
     *
     * @param DatagridInterface $datagrid
     * @param PropertyInterface $property
     * @return void
     */
    public function addProperty(DatagridInterface $datagrid, PropertyInterface $property)
    {
        $datagrid->addProperty($property);
    }

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $fieldCollection
     * @param RouteGeneratorInterface $routeGenerator
     * @param ParametersInterface $parameters
     * @param string $name
     * @param string $entityHint
     *
     * @return DatagridInterface
     */
    public function getBaseDatagrid(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $fieldCollection,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name,
        $entityHint = null
    ) {
        $formBuilder = $this->formFactory->createNamedBuilder(
            $this->getFormName($name),
            'form',
            array(),
            array('csrf_protection' => false)
        );

        $datagridClassName = $this->className;
        return new $datagridClassName(
            $query,
            $fieldCollection,
            $this->createPager($query),
            $formBuilder,
            $routeGenerator,
            $parameters,
            $name,
            $entityHint
        );
    }

    /**
     * @param string $datagridName
     * @return string
     */
    protected function getFormName($datagridName)
    {
        return $datagridName;
    }

    /**
     * @param ProxyQueryInterface $query
     * @return PagerInterface
     */
    protected function createPager(ProxyQueryInterface $query)
    {
        $pager = new Pager();
        $pager->setQuery($query);
        return $pager;
    }
}
