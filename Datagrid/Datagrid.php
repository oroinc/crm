<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Form;

use Sonata\AdminBundle\Filter\FilterInterface as SonataFilterInterface;

use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;

class Datagrid implements DatagridInterface
{
    /**
     * @var ProxyQueryInterface
     */
    protected $query;

    /**
     * @var FieldDescriptionCollection
     */
    protected $columns;

    /**
     * @var PagerInterface
     */
    protected $pager;

    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * Parameters applied flag
     *
     * @var bool
     */
    protected $parametersApplied = false;

    /**
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * Parameters binded flag
     *
     * @var bool
     */
    protected $parametersBinded = false;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var SorterInterface[]
     */
    protected $sorters = array();

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $entityHint;

    /**
     * @var ActionInterface[]
     */
    protected $rowActions;

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $columns
     * @param PagerInterface $pager
     * @param FormBuilderInterface $formBuilder
     * @param RouteGeneratorInterface $routeGenerator
     * @param ParametersInterface $parameters
     * @param string $name
     * @param string $entityHint
     */
    public function __construct(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $columns,
        PagerInterface $pager,
        FormBuilderInterface $formBuilder,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name,
        $entityHint = null
    ) {
        $this->query          = $query;
        $this->columns        = $columns;
        $this->pager          = $pager;
        $this->formBuilder    = $formBuilder;
        $this->routeGenerator = $routeGenerator;
        $this->parameters     = $parameters;
        $this->name           = $name;
        $this->entityHint     = $entityHint;
    }

    /**
     * @param SonataFilterInterface $filter
     * @return void
     */
    public function addFilter(SonataFilterInterface $filter)
    {
        $this->filters[$filter->getName()] = $filter;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param string $name
     *
     * @return SonataFilterInterface
     */
    public function getFilter($name)
    {
        return $this->hasFilter($name) ? $this->filters[$name] : null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasFilter($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * @param string $name
     */
    public function removeFilter($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * @return boolean
     */
    public function hasActiveFilters()
    {
        /** @var $filter FilterInterface */
        foreach ($this->filters as $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter)
    {
        $this->sorters[$sorter->getName()] = $sorter;
    }

    /**
     * @return array
     */
    public function getSorters()
    {
        return $this->sorters;
    }

    /**
     * @param $name
     * @return null|SorterInterface
     */
    public function getSorter($name)
    {
        if (isset($this->sorters[$name])) {
            return $this->sorters[$name];
        }

        return null;
    }

    /**
     * @return PagerInterface
     */
    public function getPager()
    {
        return $this->pager;
    }

    protected function applyParameters()
    {
        if ($this->parametersApplied) {
            return;
        }

        $this->applyFilters();
        $this->applyPager();
        $this->applySorters();

        $this->parametersApplied = true;
    }

    /**
     * Apply filter data to ProxyQuery and add form fields
     */
    protected function applyFilters()
    {
        $filterParameters = $this->parameters->get(ParametersInterface::FILTER_PARAMETERS);
        $this->formBuilder->add(ParametersInterface::FILTER_PARAMETERS, 'collection', array('type' => 'hidden'));
        $filterField = $this->formBuilder->get(ParametersInterface::FILTER_PARAMETERS);


        /** @var $filter FilterInterface */
        foreach ($this->getFilters() as $filter) {
            $filterFormName = $filter->getFormName();
            if (isset($filterParameters[$filterFormName])) {
                $filter->apply($this->query, $filterParameters[$filterFormName]);
            }

            list($type, $options) = $filter->getRenderSettings();
            $filterField->add($filterFormName, $type, $options);
        }
    }

    /**
     * Add sorters on grid and apply requested sorting
     */
    protected function applySorters()
    {
        $sortBy = $this->parameters->get(ParametersInterface::SORT_PARAMETERS);

        if (!is_array($sortBy)) {
            $sortBy = array($sortBy);
        }

        // we should retain an order in which sorters were added
        // when adding sort to query and when we creating sorters form elements
        $this->formBuilder->add(ParametersInterface::SORT_PARAMETERS, 'collection', array('type' => 'hidden'));
        $sortByField = $this->formBuilder->get(ParametersInterface::SORT_PARAMETERS);
        foreach ($sortBy as $fieldName => $direction) {
            if (isset($this->sorters[$fieldName])) {
                $this->sorters[$fieldName]->apply($this->query, $direction);
                $sortByField->add($fieldName, 'hidden');
            }
        }
    }

    protected function applyPager()
    {
        $pagerParameters = $this->parameters->get(ParametersInterface::PAGER_PARAMETERS);
        $this->pager->setPage(isset($pagerParameters['_page']) ? $pagerParameters['_page'] : 1);
        $this->pager->setMaxPerPage(!empty($pagerParameters['_per_page']) ? $pagerParameters['_per_page'] : 25);
        $this->pager->init();

        $this->formBuilder->add(ParametersInterface::PAGER_PARAMETERS, 'collection', array('type' => 'hidden'));
        $pagerField = $this->formBuilder->get(ParametersInterface::PAGER_PARAMETERS);
        $pagerField->add('_page', 'hidden');
        $pagerField->add('_per_page', 'hidden');
    }

    /**
     * Returns required form parameter value
     *
     * @param string $name
     * @return string|array|null
     */
    protected function getFormParameter($name)
    {
        $formName = $this->formBuilder->getName();
        $parametersData = $this->parameters->get($formName);
        return isset($parametersData[$name]) ? $parametersData[$name] : null;
    }

    /**
     * Bind all source parameters
     */
    protected function bindParameters()
    {
        if ($this->parametersBinded) {
            return;
        }

        $formName = $this->formBuilder->getName();
        $parametersData = $this->parameters->toArray();
        $this->form->bind($parametersData[$formName]);

        $this->parametersBinded = true;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        $this->applyParameters();

        if (!$this->form) {
            $this->form = $this->formBuilder->getForm();
        }

        $this->bindParameters();

        return $this->form;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        $this->applyParameters();
        return $this->getQuery()->execute();
    }

    /**
     * @deprecated Use applyParameters instead
     * @return void
     */
    public function buildPager()
    {
        $this->applyParameters();
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        // TODO Method return declared as array, but we have FieldDescriptionCollection
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        // TODO Interface declare array return type
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($name, $operator, $value)
    {
        // TODO: Implement setValue() method.
    }

    /**
     * @return array
     */
    public function getValues()
    {
        // TODO: Implement getValues() method.
    }

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEntityHint()
    {
        return $this->entityHint;
    }

    /**
     * @param ActionInterface $action
     * @return void
     */
    public function addRowAction(ActionInterface $action)
    {
        $this->rowActions[] = $action;
    }

    /**
     * @return ActionInterface[]
     */
    public function getRowActions()
    {
        return $this->rowActions;
    }
}
