<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Form;
use Sonata\AdminBundle\Filter\FilterInterface as SonataFilterInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface as SonataPagerInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

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
     * @var SonataPagerInterface
     */
    protected $pager;

    /**
     * @var FormBuilderInterface
     */
    protected $formBuilder;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var SorterInterface[]
     */
    protected $sorters = array();

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $columns
     * @param SonataPagerInterface $pager
     * @param FormBuilderInterface $formBuilder
     * @param ParametersInterface $parameters
     */
    public function __construct(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $columns,
        SonataPagerInterface $pager = null,
        FormBuilderInterface $formBuilder = null,
        ParametersInterface $parameters = null
    ) {
        $this->query       = $query;
        $this->columns     = $columns;
        $this->pager       = $pager;
        $this->formBuilder = $formBuilder;
        $this->parameters  = $parameters;
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
        foreach ($this->filters as $name => $filter) {
            if ($filter->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return SonataPagerInterface
     */
    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @return void
     */
    public function buildPager()
    {
        // TODO
    }

    /**
     * @param SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter)
    {
        $this->sorters[$sorter->getName()] = $sorter;
    }

    protected function applyFilters()
    {
        // TODO
    }

    protected function applySorters()
    {
        // we should retain an order in which sorters were added
//        foreach($this->parameters->getSorterParameters() as $requestedSorter) {
//            if (isset($this->sorters[$requestedSorter["name"]])) {
//                $this->sorters[$requestedSorter]->apply($this->query, $requestedSorter["direction"]);
//            }
//        }
    }

    protected function applyPager()
    {
        $this->buildPager();

        // TODO
    }

    protected function applyParameters()
    {
        $this->applyFilters();
        $this->applySorters();
        $this->applyPager();
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        $this->applyParameters();

        return $this->form;
    }

    /**
     * @return ProxyQueryInterface
     */
    public function getQuery()
    {
        $this->applyParameters();

        return $this->query;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->getQuery()->execute();
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    public function setValue($name, $operator, $value)
    {
        // TODO: Implement setValue() method.
    }
}
