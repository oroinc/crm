<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Translation\TranslatorInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Property\PropertyCollection;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridView;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\SearchBundle\Datagrid\AllResultsPager;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SearchBundle\Engine\Indexer;

class AllResultsDatagrid implements DatagridInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $entityHint;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var RouteGeneratorInterface
     */
    protected $routeGenerator;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Indexer
     */
    protected $indexer;

    /**
     * @var AllResultsPager
     */
    protected $pager;

    /**
     * @var bool
     */
    protected $pagerInitialized = false;

    /**
     * @var Result
     */
    protected $queryResult;

    /**
     * @var null|string
     */
    protected $searchString = null;

    /**
     * @var string
     */
    protected $searchEntity = '';

    /**
     * @var FieldDescriptionCollection
     */
    protected $columns;

    /**
     * @var PropertyCollection
     */
    protected $properties;

    /**
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface $translator
     * @param Indexer $indexer
     * @param RouteGeneratorInterface $routeGenerator
     * @param ParametersInterface $parameters
     * @param string $name
     * @param string $entityHint
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        Indexer $indexer,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name,
        $entityHint = null
    ) {
        $this->formFactory    = $formFactory;
        $this->translator     = $translator;
        $this->indexer        = $indexer;
        $this->routeGenerator = $routeGenerator;
        $this->parameters     = $parameters;
        $this->name           = $name;
        $this->entityHint     = $entityHint;
    }

    /**
     * @param string $searchEntity
     */
    public function setSearchEntity($searchEntity = '')
    {
        $this->searchEntity = $searchEntity;
    }

    /**
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
    }

    /**
     * @return Result
     * @throws \LogicException
     */
    protected function getQueryResult()
    {
        if (!$this->queryResult) {
            if (null === $this->searchString) {
                throw new \LogicException('There is no search string specified');
            }

            $pager = $this->createPager();

            $this->queryResult = $this->indexer->simpleSearch(
                $this->searchString,
                0,
                $pager->getMaxPerPage(),
                $this->searchEntity,
                $pager->getPage()
            );
        }

        return $this->queryResult;
    }

    /**
     * @return PagerInterface
     */
    public function getPager()
    {
        $this->createPager();
        $this->initPager($this->getQueryResult());

        return $this->pager;
    }

    /**
     * Create pager
     *
     * @return AllResultsPager
     */
    protected function createPager()
    {
        if (!$this->pager) {
            $pagerParameters = $this->parameters->get(ParametersInterface::PAGER_PARAMETERS);
            $this->pager = new AllResultsPager();
            $this->pager->setPage(isset($pagerParameters['_page']) ? $pagerParameters['_page'] : 1);
            $this->pager->setMaxPerPage(!empty($pagerParameters['_per_page']) ? $pagerParameters['_per_page'] : 10);
        }

        return $this->pager;
    }

    /**
     * Initialize pager
     */
    protected function initPager(Result $queryResult)
    {
        if ($this->pagerInitialized) {
            return;
        }

        $pager = $this->createPager();
        $pager->setQuery($queryResult);
        $pager->init();

        $this->pagerInitialized = true;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        $result = array();
        foreach ($this->getQueryResult()->getElements() as $row) {
            $result[] = new ResultRecord($row);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        if (null === $this->columns) {
            $this->columns = array();

            $description = new FieldDescription();
            $description->setName('description');
            $description->setOptions(
                array(
                    'type'        => FieldDescriptionInterface::TYPE_TEXT,
                    'label'       => '',
                    'field_name'  => 'recordText',
                    'required'    => false,
                    'sortable'    => false,
                    'filterable'  => false,
                    'show_filter' => false,
                )
            );

            $this->columns[] = $description;
        }

        return $this->columns;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        if (null === $this->properties) {
            $this->properties = new PropertyCollection();
            /** @var $field FieldDescriptionInterface */
            foreach ($this->getColumns() as $field) {
                $this->addProperty($field->getProperty());
            }
        }

        return $this->properties;
    }

    /**
     * @param PropertyInterface $property
     * @return void
     */
    public function addProperty(PropertyInterface $property)
    {
        $this->properties->add($property);
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
     * @return DatagridView
     */
    public function createView()
    {
        return new DatagridView($this);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters->toArray();
    }

    /**
     * @return RouteGeneratorInterface
     */
    public function getRouteGenerator()
    {
        return $this->routeGenerator;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getForm()
    {
        if (!$this->form) {
            $formBuilder = $this->formFactory->createNamedBuilder(
                $this->getName(),
                'form',
                array(),
                array('csrf_protection' => false)
            );
            $this->form = $formBuilder->getForm();
        }

        return $this->form;
    }

    /**
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function getQuery()
    {
        return null;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return array();
    }

    /**
     * @return SorterInterface[]
     */
    public function getSorters()
    {
        return array();
    }

    /**
     * @return ActionInterface[]
     */
    public function getRowActions()
    {
        return array();
    }

    /**
     * @param string $name
     * @return null|SorterInterface
     */
    public function getSorter($name)
    {
        return null;
    }

    /**
     * @return void
     */
    public function buildPager()
    {
        $this->getPager();
    }

    /**
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    public function setValue($name, $operator, $value)
    {
    }

    /**
     * @return boolean
     */
    public function hasActiveFilters()
    {
        return false;
    }

    /**
     * @param string $name
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function getFilter($name)
    {
        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasFilter($name)
    {
        return false;
    }

    /**
     * @param string $name
     */
    public function removeFilter($name)
    {
    }

    /**
     * @param \Sonata\AdminBundle\Filter\FilterInterface $filter
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function addFilter(FilterInterface $filter)
    {
    }

    /**
     * @param SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter)
    {
    }

    /**
     * @param ActionInterface $action
     * @return void
     */
    public function addRowAction(ActionInterface $action)
    {
    }
}
