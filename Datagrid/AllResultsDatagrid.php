<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Translation\TranslatorInterface;
use Sonata\AdminBundle\Filter\FilterInterface;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Property\PropertyInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridView;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
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
     * @var Result
     */
    protected $queryResult;

    /**
     * @var null|string
     */
    protected $searchString = null;

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
                null,
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
        if (!$this->pager) {
            $this->createPager();
            $this->initPager($this->getQueryResult());
        }

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
        $pager = $this->getPager();
        $pager->setQuery($queryResult);
        $pager->init();
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
        // TODO implement one result column
        return array();
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
     * @deprected Should not be used
     *
     * @return void
     */
    public function buildPager()
    {
        $this->getPager();
    }

    /**
     * @deprected Should not be used
     *
     * @param string $name
     * @param string $operator
     * @param mixed  $value
     */
    public function setValue($name, $operator, $value)
    {
    }

    /**
     * @deprected Should not be used
     *
     * @return boolean
     */
    public function hasActiveFilters()
    {
        return false;
    }

    /**
     * @deprected Should not be used
     *
     * @param string $name
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function getFilter($name)
    {
        return null;
    }

    /**
     * @deprected Should not be used
     *
     * @param string $name
     * @return bool
     */
    public function hasFilter($name)
    {
        return false;
    }

    /**
     * @deprected Should not be used
     *
     * @param string $name
     */
    public function removeFilter($name)
    {
    }

    /**
     * @deprected Should not be used
     *
     * @param \Sonata\AdminBundle\Filter\FilterInterface $filter
     * @return \Sonata\AdminBundle\Filter\FilterInterface
     */
    public function addFilter(FilterInterface $filter)
    {
    }

    /**
     * @deprected Should not be used
     *
     * @param PropertyInterface $property
     * @return void
     */
    public function addProperty(PropertyInterface $property)
    {
    }

    /**
     * @deprected Should not be used
     *
     * @param SorterInterface $sorter
     * @return void
     */
    public function addSorter(SorterInterface $sorter)
    {
    }

    /**
     * @deprected Should not be used
     *
     * @param ActionInterface $action
     * @return void
     */
    public function addRowAction(ActionInterface $action)
    {
    }
}
