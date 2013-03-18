<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\Datagrid;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;

class DatagridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test grid name
     */
    const TEST_NAME        = 'test_grid_name';
    const TEST_FILTER_NAME = 'test_filter_name';
    const TEST_SORTER_NAME = 'test_sorter_name';

    /**
     * @var Datagrid
     */
    protected $model;

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

    public function testGetName()
    {
        $this->initializeDatagridMock(array('name' => self::TEST_NAME));
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
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
            array('getName')
        );
        $filterMock->expects($this->any())
            ->method('getName', 'isActive')
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
            array('getName')
        );
        $sorterMock->expects($this->any())
            ->method('getName', 'isActive')
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
}
