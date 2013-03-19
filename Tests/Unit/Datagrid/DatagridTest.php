<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class DatagridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test grid name
     */
    const TEST_NAME             = 'test_grid_name';
    const TEST_FILTER_NAME      = 'test_filter_name';
    const TEST_SORTER_NAME      = 'test_sorter_name';
    const TEST_SORTER_DIRECTION = 'test_sorter_direction';
    const TEST_FORM_NAME        = 'test_form_name';

    const TEST_ACTIVE_FILTER_NAME   = 'active_filter_name';
    const TEST_INACTIVE_FILTER_NAME = 'inactive_filter_name';
    const TEST_ACTIVE_FILTER_TYPE   = 'active_filter_type';
    const TEST_INACTIVE_FILTER_TYPE = 'inactive_filter_type';

    const TEST_PAGE = 3;
    const TEST_PER_PAGE = 100;

    /**
     * @var Datagrid
     */
    protected $model;

    /**
     * @var array
     */
    protected $testParameters = array(
        ParametersInterface::FILTER_PARAMETERS => array(
            self::TEST_ACTIVE_FILTER_NAME => array(
                'type'    => self::TEST_ACTIVE_FILTER_TYPE,
                'options' => array('active', 'filter', 'options'),
            )
        ),
        ParametersInterface::PAGER_PARAMETERS => array(
            '_page'     => self::TEST_PAGE,
            '_per_page' => self::TEST_PER_PAGE,
        ),
        ParametersInterface::SORT_PARAMETERS => array(
            self::TEST_SORTER_NAME => self::TEST_SORTER_DIRECTION
        ),
    );

    /**
     * @var array
     */
    protected $testColumns = array(
        'column_1' => array('value_1'),
        'column_2' => array('value_2'),
    );

    /**
     * @var array
     */
    protected $testResult = array('test', 'result', 'data');

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * Prepare all constructor argument mocks for datagrid and create
     *
     * @param array $arguments
     */
    protected function initializeDatagridMock($arguments = array())
    {
        $defaultArguments = array(
            'query'          => $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface'),
            'columns'        => $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescriptionCollection'),
            'pager'          => $this->getMock('Oro\Bundle\GridBundle\Datagrid\PagerInterface'),
            'formBuilder'    => $this->getMock('Symfony\Component\Form\FormBuilder', array(), array(), '', false),
            'routeGenerator' => $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface'),
            'parameters'     => $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface'),
            'name'           => null,
            'entityHint'     => null,
        );

        $arguments = array_merge($defaultArguments, $arguments);

        $this->model = new Datagrid(
            $arguments['query'],
            $arguments['columns'],
            $arguments['pager'],
            $arguments['formBuilder'],
            $arguments['routeGenerator'],
            $arguments['parameters'],
            $arguments['name'],
            $arguments['entityHint']
        );
    }

    /**
     * @param string $filterName
     * @return FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilterMock($filterName)
    {
        $filterMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\FilterInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getName', 'isActive', 'getFormName', 'apply', 'getRenderSettings')
        );
        $filterMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($filterName));

        return $filterMock;
    }

    public function testAddFilter()
    {
        $filterMock = $this->getFilterMock(self::TEST_FILTER_NAME);
        $this->initializeDatagridMock();

        $this->assertAttributeEmpty('filters', $this->model);
        $this->model->addFilter($filterMock);
        $this->assertAttributeEquals(array(self::TEST_FILTER_NAME => $filterMock), 'filters', $this->model);
    }

    public function testGetFilters()
    {
        $this->initializeDatagridMock();

        $expectedFilters = array(
            'filter_name_1' => null,
            'filter_name_2' => null,
        );
        foreach (array_keys($expectedFilters) as $filterName) {
            $filterMock = $this->getFilterMock($filterName);
            $this->model->addFilter($filterMock);
            $expectedFilters[$filterName] = $filterMock;
        }

        $this->assertEquals($expectedFilters, $this->model->getFilters());
    }

    public function testGetFilter()
    {
        $filterMock = $this->getFilterMock(self::TEST_FILTER_NAME);
        $this->initializeDatagridMock();

        $this->assertNull($this->model->getFilter(self::TEST_FILTER_NAME));
        $this->model->addFilter($filterMock);
        $this->assertEquals($filterMock, $this->model->getFilter(self::TEST_FILTER_NAME));
    }

    public function testHasFilter()
    {
        $filterMock = $this->getFilterMock(self::TEST_FILTER_NAME);
        $this->initializeDatagridMock();

        $this->assertFalse($this->model->hasFilter(self::TEST_FILTER_NAME));
        $this->model->addFilter($filterMock);
        $this->assertTrue($this->model->hasFilter(self::TEST_FILTER_NAME));
    }

    public function testRemoveFilter()
    {
        $filterMock = $this->getFilterMock(self::TEST_FILTER_NAME);
        $this->initializeDatagridMock();
        $this->model->addFilter($filterMock);

        $this->assertTrue($this->model->hasFilter(self::TEST_FILTER_NAME));
        $this->model->removeFilter(self::TEST_FILTER_NAME);
        $this->assertFalse($this->model->hasFilter(self::TEST_FILTER_NAME));
    }

    public function hasActiveFiltersDataProvider()
    {
        return array(
            'has_active_filters' => array(
                '$sourceFilters' => array(
                    'filter_name_1' => false,
                    'filter_name_2' => true,
                ),
                '$isActive' => true,
            ),
            'has_not_active_filters' => array(
                '$sourceFilters' => array(
                    'filter_name_1' => false,
                    'filter_name_2' => false,
                ),
                '$isActive' => false,
            ),
        );
    }

    /**
     * @param array $sourceFilters
     * @param boolean $isActive
     * @dataProvider hasActiveFiltersDataProvider
     */
    public function testHasActiveFilters(array $sourceFilters, $isActive)
    {
        $this->initializeDatagridMock();

        foreach ($sourceFilters as $filterName => $isActive) {
            /** @var $filterMock \PHPUnit_Framework_MockObject_MockObject */
            $filterMock = $this->getFilterMock($filterName);
            $filterMock->expects($this->any())
                ->method('isActive')
                ->will($this->returnValue($isActive));
            $this->model->addFilter($filterMock);
        }

        if ($isActive) {
            $this->assertTrue($this->model->hasActiveFilters());
        } else {
            $this->assertFalse($this->model->hasActiveFilters());
        }
    }

    /**
     * @param string $sorterName
     * @return SorterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSorterMock($sorterName)
    {
        $sorterMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Sorter\SorterInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getName', 'apply')
        );
        $sorterMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($sorterName));

        return $sorterMock;
    }

    public function testAddSorter()
    {
        $sorterMock = $this->getSorterMock(self::TEST_SORTER_NAME);
        $this->initializeDatagridMock();

        $this->assertAttributeEmpty('sorters', $this->model);
        $this->model->addSorter($sorterMock);
        $this->assertAttributeEquals(array(self::TEST_SORTER_NAME => $sorterMock), 'sorters', $this->model);
    }

    public function testGetSorters()
    {
        $this->initializeDatagridMock();

        $expectedSorters = array(
            'sorter_name_1' => null,
            'sorter_name_2' => null,
        );
        foreach (array_keys($expectedSorters) as $sorterName) {
            $sorterMock = $this->getSorterMock($sorterName);
            $this->model->addSorter($sorterMock);
            $expectedSorters[$sorterName] = $sorterMock;
        }

        $this->assertEquals($expectedSorters, $this->model->getSorters());
    }

    public function testGetSorter()
    {
        $sorterMock = $this->getSorterMock(self::TEST_SORTER_NAME);
        $this->initializeDatagridMock();

        $this->assertNull($this->model->getSorter(self::TEST_SORTER_NAME));
        $this->model->addSorter($sorterMock);
        $this->assertEquals($sorterMock, $this->model->getSorter(self::TEST_SORTER_NAME));
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter($name)
    {
        return isset($this->testParameters[$name]) ? $this->testParameters[$name] : null;
    }

    /**
     * @param FormBuilder $filterFieldMock
     * @param FormBuilder $pagerFilterMock
     * @param FormBuilder $sorterFieldMock
     * @param Form $formMock
     * @return FormBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormBuilderMock(
        FormBuilder $filterFieldMock,
        FormBuilder $pagerFilterMock,
        FormBuilder $sorterFieldMock,
        Form $formMock
    ) {
        $formBuilderMock = $this->getMock(
            'Symfony\Component\Form\FormBuilder',
            array('add', 'get', 'getForm', 'getName'),
            array(),
            '',
            false
        );
        $formBuilderMock->expects($this->at(0))
            ->method('add')
            ->with(ParametersInterface::FILTER_PARAMETERS, 'collection', array('type' => 'hidden'));
        $formBuilderMock->expects($this->at(1))
            ->method('get')
            ->with(ParametersInterface::FILTER_PARAMETERS)
            ->will($this->returnValue($filterFieldMock));
        $formBuilderMock->expects($this->at(2))
            ->method('add')
            ->with(ParametersInterface::PAGER_PARAMETERS, 'collection', array('type' => 'hidden'));
        $formBuilderMock->expects($this->at(3))
            ->method('get')
            ->with(ParametersInterface::PAGER_PARAMETERS)
            ->will($this->returnValue($pagerFilterMock));
        $formBuilderMock->expects($this->at(4))
            ->method('add')
            ->with(ParametersInterface::SORT_PARAMETERS, 'collection', array('type' => 'hidden'));
        $formBuilderMock->expects($this->at(5))
            ->method('get')
            ->with(ParametersInterface::SORT_PARAMETERS)
            ->will($this->returnValue($sorterFieldMock));
        $formBuilderMock->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($formMock));
        $formBuilderMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::TEST_FORM_NAME));

        return $formBuilderMock;
    }

    /**
     * @return array
     */
    protected function prepareDatagridMocks()
    {
        $proxyQueryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('execute')
        );

        $parametersMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ParametersInterface',
            array(),
            '',
            false,
            true,
            true,
            array('get', 'toArray')
        );
        $parametersMock->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this, 'getParameter')));
        $parametersMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(array(self::TEST_FORM_NAME => $this->testParameters)));

        $filterFieldMock = $this->getMock('Symfony\Component\Form\FormBuilder', array('add'), array(), '', false);
        $filterFieldMock->expects($this->at(0))
            ->method('add')
            ->with(
                self::TEST_ACTIVE_FILTER_NAME,
                self::TEST_ACTIVE_FILTER_TYPE,
                array('active', 'filter', 'options')
            );
        $filterFieldMock->expects($this->at(1))
            ->method('add')
            ->with(
                self::TEST_INACTIVE_FILTER_NAME,
                self::TEST_INACTIVE_FILTER_TYPE,
                array('inactive', 'filter', 'options')
            );

        $pagerFilterMock = $this->getMock('Symfony\Component\Form\FormBuilder', array('add'), array(), '', false);
        $pagerFilterMock->expects($this->at(0))->method('add')->with('_page', 'hidden');
        $pagerFilterMock->expects($this->at(1))->method('add')->with('_per_page', 'hidden');

        $sorterFieldMock = $this->getMock('Symfony\Component\Form\FormBuilder', array('add'), array(), '', false);
        $sorterFieldMock->expects($this->once())
            ->method('add')
            ->with(self::TEST_SORTER_NAME, 'hidden');

        $formMock = $this->getMock('Symfony\Component\Form\Form', array('bind'), array(), '', false);
        $formMock->expects($this->any())
            ->method('bind')
            ->will($this->returnValue($this->testParameters));

        $formBuilderMock = $this->getFormBuilderMock($filterFieldMock, $pagerFilterMock, $sorterFieldMock, $formMock);

        $pagerMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\PagerInterface',
            array(),
            '',
            false,
            true,
            true,
            array('setPage', 'setMaxPerPage', 'init')
        );
        $pagerMock->expects($this->once())->method('setPage')->with(self::TEST_PAGE);
        $pagerMock->expects($this->once())->method('setMaxPerPage')->with(self::TEST_PER_PAGE);
        $pagerMock->expects($this->once())->method('init');

        return array(
            'query'       => $proxyQueryMock,
            'pager'       => $pagerMock,
            'form'        => $formMock,
            'formBuilder' => $formBuilderMock,
            'parameters'  => $parametersMock,
        );
    }

    /**
     * @param ProxyQueryInterface $proxyQueryMock
     */
    protected function addFilterMocks(ProxyQueryInterface $proxyQueryMock)
    {
        $filterParameters = $this->testParameters[ParametersInterface::FILTER_PARAMETERS];

        /** @var $activeFilterMock \PHPUnit_Framework_MockObject_MockObject */
        $activeFilterMock = $this->getFilterMock(self::TEST_ACTIVE_FILTER_NAME);
        $activeFilterMock->expects($this->once())
            ->method('getFormName')
            ->will($this->returnValue(self::TEST_ACTIVE_FILTER_NAME));
        $activeFilterMock->expects($this->once())
            ->method('apply')
            ->with($proxyQueryMock, $filterParameters[self::TEST_ACTIVE_FILTER_NAME]);
        $activeFilterMock->expects($this->once())
            ->method('getRenderSettings')
            ->will(
                $this->returnValue(
                    array(
                        self::TEST_ACTIVE_FILTER_TYPE,
                        $filterParameters[self::TEST_ACTIVE_FILTER_NAME]['options']
                    )
                )
            );
        /** @var $inactiveFilterMock \PHPUnit_Framework_MockObject_MockObject */
        $inactiveFilterMock = $this->getFilterMock(self::TEST_INACTIVE_FILTER_NAME);
        $inactiveFilterMock->expects($this->once())
            ->method('getFormName')
            ->will($this->returnValue(self::TEST_INACTIVE_FILTER_NAME));
        $inactiveFilterMock->expects($this->never())
            ->method('apply');
        $inactiveFilterMock->expects($this->once())
            ->method('getRenderSettings')
            ->will(
                $this->returnValue(
                    array(
                        self::TEST_INACTIVE_FILTER_TYPE,
                        array('inactive', 'filter', 'options'),
                    )
                )
            );

        $this->model->addFilter($activeFilterMock);
        $this->model->addFilter($inactiveFilterMock);
    }

    /**
     * @param ProxyQueryInterface $proxyQueryMock
     */
    protected function addSorterMocks(ProxyQueryInterface $proxyQueryMock)
    {
        $sorterParameters = $this->testParameters[ParametersInterface::SORT_PARAMETERS];

        /** @var $sorterMock \PHPUnit_Framework_MockObject_MockObject */
        $sorterMock = $this->getSorterMock(self::TEST_SORTER_NAME);
        $sorterMock->expects($this->once())
            ->method('apply')
            ->with($proxyQueryMock, $sorterParameters[self::TEST_SORTER_NAME]);

        $this->model->addSorter($sorterMock);
    }

    public function testGetForm()
    {
        $datagridMocks = $this->prepareDatagridMocks();
        $this->initializeDatagridMock(
            array(
                'query'       => $datagridMocks['query'],
                'pager'       => $datagridMocks['pager'],
                'formBuilder' => $datagridMocks['formBuilder'],
                'parameters'  => $datagridMocks['parameters'],
            )
        );

        $this->addFilterMocks($datagridMocks['query']);
        $this->addSorterMocks($datagridMocks['query']);

        $this->assertAttributeEquals(false, 'parametersApplied', $this->model);
        $this->assertAttributeEmpty('form', $this->model);
        $this->assertAttributeEquals(false, 'parametersBinded', $this->model);

        // all actions must be executed only at first run
        $this->assertEquals($datagridMocks['form'], $this->model->getForm());
        $this->assertEquals($datagridMocks['form'], $this->model->getForm());

        $this->assertAttributeEquals(true, 'parametersApplied', $this->model);
        $this->assertAttributeEquals($datagridMocks['form'], 'form', $this->model);
        $this->assertAttributeEquals(true, 'parametersBinded', $this->model);
    }

    public function testBuildPager()
    {
        $datagridMocks = $this->prepareDatagridMocks();
        $this->initializeDatagridMock(
            array(
                'query'       => $datagridMocks['query'],
                'pager'       => $datagridMocks['pager'],
                'formBuilder' => $datagridMocks['formBuilder'],
                'parameters'  => $datagridMocks['parameters'],
            )
        );

        $this->addFilterMocks($datagridMocks['query']);
        $this->addSorterMocks($datagridMocks['query']);

        $this->model->buildPager();
    }

    public function testGetQuery()
    {
        $queryMock = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface', array(), array(), '', false);
        $this->initializeDatagridMock(array('query' => $queryMock));

        $this->assertEquals($queryMock, $this->model->getQuery());
    }

    public function testGetResults()
    {
        $datagridMocks = $this->prepareDatagridMocks();
        /** @var $proxyQueryMock \PHPUnit_Framework_MockObject_MockObject */
        $proxyQueryMock = $datagridMocks['query'];
        $proxyQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($this->testResult));

        $this->initializeDatagridMock(
            array(
                'query'       => $proxyQueryMock,
                'pager'       => $datagridMocks['pager'],
                'formBuilder' => $datagridMocks['formBuilder'],
                'parameters'  => $datagridMocks['parameters'],
            )
        );

        $this->addFilterMocks($proxyQueryMock);
        $this->addSorterMocks($proxyQueryMock);

        $this->assertEquals($this->testResult, $this->model->getResults());
    }

    public function testGetColumns()
    {
        $columns = $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescriptionCollection', array('getElements'));
        $columns->expects($this->once())
            ->method('getElements')
            ->will($this->returnValue($this->testColumns));

        $this->initializeDatagridMock(array('columns' => $columns));

        $this->assertEquals($this->testColumns, $this->model->getColumns());
    }

    /**
     * @return ParametersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareParametersMock()
    {
        $parameters = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ParametersInterface',
            array(),
            '',
            false,
            true,
            true,
            array('toArray')
        );
        $parameters->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($this->testParameters));

        return $parameters;
    }

    public function testGetParameters()
    {
        $parameters = $this->prepareParametersMock();
        $this->initializeDatagridMock(array('parameters' => $parameters));
        $this->assertEquals($this->testParameters, $this->model->getParameters());
    }

    public function testGetValues()
    {
        $parameters = $this->prepareParametersMock();
        $this->initializeDatagridMock(array('parameters' => $parameters));
        $this->assertEquals($this->testParameters, $this->model->getValues());
    }

    public function testGetRouteGenerator()
    {
        $routeGenerator = $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $this->initializeDatagridMock(array('routeGenerator' => $routeGenerator));
        $this->assertEquals($routeGenerator, $this->model->getRouteGenerator());
    }

    public function testGetName()
    {
        $this->initializeDatagridMock(array('name' => self::TEST_NAME));
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    public function testGetEntityHint()
    {
        $this->initializeDatagridMock(array('entityHint' => self::TEST_NAME));
        $this->assertEquals(self::TEST_NAME, $this->model->getEntityHint());
    }

    public function testAddRowAction()
    {
        $actionMock = $this->getMock('Oro\Bundle\GridBundle\Action\ActionInterface');
        $this->initializeDatagridMock();

        $this->assertAttributeEmpty('rowActions', $this->model);
        $this->model->addRowAction($actionMock);
        $this->assertAttributeEquals(array($actionMock), 'rowActions', $this->model);
    }

    public function testGetRowActions()
    {
        $this->initializeDatagridMock();

        $expectedActions = array();
        for ($i = 0; $i < 5; $i++) {
            $actionMock = $this->getMock('Oro\Bundle\GridBundle\Action\ActionInterface');
            $expectedActions[] = $actionMock;
            $this->model->addRowAction($actionMock);
        }

        $this->assertEquals($expectedActions, $this->model->getRowActions());
    }

    public function testSetValue()
    {
        // method is empty, do nothing
        $this->initializeDatagridMock();
        $this->model->setValue(self::TEST_SORTER_NAME, null, self::TEST_SORTER_DIRECTION);
    }
}
