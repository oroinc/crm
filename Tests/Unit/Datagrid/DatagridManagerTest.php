<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;

class DatagridManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME                      = 'test_name';
    const TEST_HINT                      = 'test_hint';
    const TEST_COMPLEX_FILTERABLE_FIELD  = 'test_complex_sortable_field';
    const TEST_FILTERABLE_SORTABLE_FIELD = 'test_filterable_sortable_field';
    const TEST_SORTABLE_FIELD            = 'test_sortable_field';

    /**
     * @var DatagridManager
     */
    protected $model;

    /**
     * @var array
     */
    protected $testFields = array(
        self::TEST_COMPLEX_FILTERABLE_FIELD => array(
            'option_1'     => 'value_1',
            'filterable'   => 'true',
            'complex_data' => true,
            'field_name'   => self::TEST_COMPLEX_FILTERABLE_FIELD
        ),
        self::TEST_SORTABLE_FIELD => array(
            'option_2' => 'value_2',
            'sortable' => true),
        self::TEST_FILTERABLE_SORTABLE_FIELD => array(
            'option_3'   => 'value_3',
            'filterable' => 'true',
            'sortable'   => true),
        'simple_field' => array(
            'option_4' => 'value_4'
        ),
    );

    /**
     * @var array
     */
    protected $testRowActions = array(
        1 => array('row_1' => 'parameter_1'),
        2 => array('row_2' => 'parameter_2'),
    );

    protected function setUp()
    {
        $this->model = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\DatagridManager');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetDatagridBuilder()
    {
        $datagridBuilderMock = $this->getMock('Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface');

        $this->assertAttributeEmpty('datagridBuilder', $this->model);
        $this->model->setDatagridBuilder($datagridBuilderMock);
        $this->assertAttributeEquals($datagridBuilderMock, 'datagridBuilder', $this->model);
    }

    public function testSetListBuilder()
    {
        $listBuilderMock = $this->getMock('Oro\Bundle\GridBundle\Builder\ListBuilderInterface');

        $this->assertAttributeEmpty('listBuilder', $this->model);
        $this->model->setListBuilder($listBuilderMock);
        $this->assertAttributeEquals($listBuilderMock, 'listBuilder', $this->model);
    }

    public function testSetQueryFactory()
    {
        $queryFactoryMock = $this->getMock('Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface');

        $this->assertAttributeEmpty('queryFactory', $this->model);
        $this->model->setQueryFactory($queryFactoryMock);
        $this->assertAttributeEquals($queryFactoryMock, 'queryFactory', $this->model);
    }

    public function testSetTranslator()
    {
        $translatorMock = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->assertAttributeEmpty('translator', $this->model);
        $this->model->setTranslator($translatorMock);
        $this->assertAttributeEquals($translatorMock, 'translator', $this->model);
    }

    public function testSetValidator()
    {
        $validatorMock = $this->getMock('Symfony\Component\Validator\ValidatorInterface');

        $this->assertAttributeEmpty('validator', $this->model);
        $this->model->setValidator($validatorMock);
        $this->assertAttributeEquals($validatorMock, 'validator', $this->model);
    }

    public function testSetRouteGenerator()
    {
        $routeGeneratorMock = $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');

        $this->assertAttributeEmpty('routeGenerator', $this->model);
        $this->model->setRouteGenerator($routeGeneratorMock);
        $this->assertAttributeEquals($routeGeneratorMock, 'routeGenerator', $this->model);
    }

    public function testSetParameters()
    {
        $parametersMock = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');

        $this->assertAttributeEmpty('parameters', $this->model);
        $this->model->setParameters($parametersMock);
        $this->assertAttributeEquals($parametersMock, 'parameters', $this->model);
    }

    public function testSetName()
    {
        $this->assertAttributeEmpty('name', $this->model);
        $this->model->setName(self::TEST_NAME);
        $this->assertAttributeEquals(self::TEST_NAME, 'name', $this->model);
    }

    public function testSetEntityHint()
    {
        $this->assertAttributeEmpty('entityHint', $this->model);
        $this->model->setEntityHint(self::TEST_HINT);
        $this->assertAttributeEquals(self::TEST_HINT, 'entityHint', $this->model);
    }

    /**
     * Generate fields, filters, sorters
     *
     * @param boolean $customValues
     */
    protected function prepareDatagridManagerForGetDatagrid($customValues = false)
    {
        // convert fields to field descriptions
        foreach ($this->testFields as $fieldName => $fieldOptions) {
            if (is_array($fieldOptions)) {
                $field = new FieldDescription();
                $field->setName($fieldName);
                $field->setOptions($fieldOptions);
                $this->testFields[$fieldName] = $field;
            }
        }

        $mockedMethods = array('getListFields');
        if ($customValues) {
            $mockedMethods = array_merge($mockedMethods, array('getFilters', 'getSorters', 'getRowActions'));
        }

        $datagridManager = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridManager',
            array(),
            '',
            false,
            true,
            true,
            $mockedMethods
        );
        $datagridManager->expects($this->any())
            ->method('getListFields')
            ->will($this->returnValue($this->testFields));

        if ($customValues) {
            $filterableFields = array();
            /** @var $fieldDescription FieldDescription */
            foreach ($this->testFields as $fieldDescription) {
                if ($fieldDescription->getOption('filterable')) {
                    $filterableFields[] = $fieldDescription;
                }
            }

            $sortableFields = array();
            /** @var $fieldDescription FieldDescription */
            foreach ($this->testFields as $fieldDescription) {
                if ($fieldDescription->getOption('sortable')) {
                    $sortableFields[] = $fieldDescription;
                }
            }

            $datagridManager->expects($this->any())
                ->method('getListFields')
                ->will($this->returnValue($this->testFields));
            $datagridManager->expects($this->any())
                ->method('getFilters')
                ->will($this->returnValue($filterableFields));
            $datagridManager->expects($this->any())
                ->method('getSorters')
                ->will($this->returnValue($sortableFields));
            $datagridManager->expects($this->any())
                ->method('getRowActions')
                ->will($this->returnValue($this->testRowActions));
        }

        $this->model = $datagridManager;
    }

    /**
     * @return array
     */
    protected function getFilterListParameters()
    {
        $listCollection = new FieldDescriptionCollection();

        $listBuilderMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Builder\ListBuilderInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getBaseList')
        );
        $listBuilderMock->expects($this->once())
            ->method('getBaseList')
            ->will($this->returnValue($listCollection));

        return array(
            'list_collection' => $listCollection,
            'list_builder'    => $listBuilderMock,
        );
    }

    public function testGetDatagrid()
    {
        $this->prepareDatagridManagerForGetDatagrid(true);

        $datagridMock       = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $queryMock          = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $routeGeneratorMock = $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $parametersMock     = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');

        $filterListParameters = $this->getFilterListParameters();
        $listCollection  = $filterListParameters['list_collection'];
        $listBuilderMock = $filterListParameters['list_builder'];

        $queryFactoryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('createQuery')
        );
        $queryFactoryMock->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($queryMock));

        $datagridBuilderMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface',
            array(),
            '',
            false,
            true,
            true,
            array('addComplexField', 'getBaseDatagrid', 'addFilter', 'addSorter', 'addRowAction')
        );
        $datagridBuilderMock->expects($this->once())
            ->method('addComplexField')
            ->with(self::TEST_COMPLEX_FILTERABLE_FIELD);
        $datagridBuilderMock->expects($this->once())
            ->method('getBaseDatagrid')
            ->with(
                $queryMock,
                $listCollection,
                $routeGeneratorMock,
                $parametersMock,
                self::TEST_NAME,
                self::TEST_HINT
            )
            ->will($this->returnValue($datagridMock));
        $datagridBuilderMock->expects($this->at(2))
            ->method('addFilter')
            ->with($datagridMock, $this->testFields[self::TEST_COMPLEX_FILTERABLE_FIELD]);
        $datagridBuilderMock->expects($this->at(3))
            ->method('addFilter')
            ->with($datagridMock, $this->testFields[self::TEST_FILTERABLE_SORTABLE_FIELD]);
        $datagridBuilderMock->expects($this->at(4))
            ->method('addSorter')
            ->with($datagridMock, $this->testFields[self::TEST_SORTABLE_FIELD]);
        $datagridBuilderMock->expects($this->at(5))
            ->method('addSorter')
            ->with($datagridMock, $this->testFields[self::TEST_FILTERABLE_SORTABLE_FIELD]);
        $datagridBuilderMock->expects($this->at(6))
            ->method('addRowAction')
            ->with($datagridMock, $this->testRowActions[1]);
        $datagridBuilderMock->expects($this->at(7))
            ->method('addRowAction')
            ->with($datagridMock, $this->testRowActions[2]);

        $this->model->setDatagridBuilder($datagridBuilderMock);
        $this->model->setListBuilder($listBuilderMock);
        $this->model->setQueryFactory($queryFactoryMock);
        $this->model->setRouteGenerator($routeGeneratorMock);
        $this->model->setParameters($parametersMock);
        $this->model->setName(self::TEST_NAME);
        $this->model->setEntityHint(self::TEST_HINT);

        $this->assertEquals($datagridMock, $this->model->getDatagrid());
        $this->assertEquals($this->testFields, $listCollection->getElements());
    }

    public function testGetDatagridWithDefaultValues()
    {
        $this->prepareDatagridManagerForGetDatagrid();

        $datagridMock       = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $queryMock          = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $routeGeneratorMock = $this->getMock('Oro\Bundle\GridBundle\Route\RouteGeneratorInterface');
        $parametersMock     = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');

        $filterListParameters = $this->getFilterListParameters();
        $listCollection  = $filterListParameters['list_collection'];
        $listBuilderMock = $filterListParameters['list_builder'];

        $queryFactoryMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('createQuery')
        );
        $queryFactoryMock->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($queryMock));

        $datagridBuilderMock = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface',
            array(),
            '',
            false,
            true,
            true,
            array('addComplexField', 'getBaseDatagrid', 'addFilter', 'addSorter', 'addRowAction')
        );
        $datagridBuilderMock->expects($this->once())
            ->method('getBaseDatagrid')
            ->will($this->returnValue($datagridMock));

        // default filters, sorters and row actions are empty arrays
        $datagridBuilderMock->expects($this->never())->method('addFilter');
        $datagridBuilderMock->expects($this->never())->method('addSorter');
        $datagridBuilderMock->expects($this->never())->method('addRowAction');

        $this->model->setDatagridBuilder($datagridBuilderMock);
        $this->model->setListBuilder($listBuilderMock);
        $this->model->setQueryFactory($queryFactoryMock);
        $this->model->setRouteGenerator($routeGeneratorMock);
        $this->model->setParameters($parametersMock);
        $this->model->setName(self::TEST_NAME);
        $this->model->setEntityHint(self::TEST_HINT);

        $this->assertEquals($datagridMock, $this->model->getDatagrid());
        $this->assertEquals($this->testFields, $listCollection->getElements());
    }
}
