<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Form\Form;
use Sonata\AdminBundle\Datagrid\DatagridInterface as BaseDatagridInterface;
use Sonata\AdminBundle\Filter\FilterInterface as SonataFilterInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

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
     * @var array
     */
    protected $values;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $columns
     * @param PagerInterface $pager
     * @param FormBuilderInterface $formBuilder
     * @param array $values
     */
    public function __construct(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $columns,
        PagerInterface $pager = null,
        FormBuilderInterface $formBuilder = null,
        array $values = array()
    ) {
        $this->query       = $query;
        $this->columns     = $columns;
        $this->pager       = $pager;
        $this->formBuilder = $formBuilder;
        $this->values      = $values;
    }

    /**
     * @param SonataFilterInterface $filter
     * @return SonataFilterInterface
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
     * @return \Sonata\AdminBundle\Filter\FilterInterface
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
    }

    /**
     * @return PagerInterface
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

    protected function applyFilters()
    {
        // TODO
    }

    protected function applySorters()
    {
        // TODO
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
        // TODO: Implement getResults() method.
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
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
