<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Sorter\ORM;

use Oro\Bundle\GridBundle\Sorter\ORM\Sorter;
use Oro\Bundle\GridBundle\Field\FieldDescription;

class SorterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME           = 'name';
    const TEST_ALIAS          = 'alias';
    const TEST_MAIN_ALIAS     = 'main';
    const TEST_ASC_DIRECTION  = 'ASC';
    const TEST_DESC_DIRECTION = 'DESC';
    /**#@-*/

    /**
     * @var Sorter
     */
    protected $model;

    /**
     * @var FieldDescription
     */
    protected $fieldDescription;

    protected function setUp()
    {
        $this->model = new Sorter();
    }

    protected function tearDown()
    {
        unset($this->model);
        unset($this->fieldDescription);
    }

    public function initializeFieldDescription($name, $additionalOptions = array())
    {
        $this->fieldDescription = new FieldDescription();
        $this->fieldDescription->setName($name);

        $options = array('field_name' => $name);
        $this->fieldDescription->setOptions(array_merge($options, $additionalOptions));
    }

    public function testInitialize()
    {
        $this->initializeFieldDescription(self::TEST_NAME);

        $this->model->initialize($this->fieldDescription, self::TEST_ASC_DIRECTION);

        $this->assertAttributeEquals($this->fieldDescription, 'field', $this->model);
        $this->assertAttributeEquals(self::TEST_ASC_DIRECTION, 'direction', $this->model);
    }

    /**
     * @depends testInitialize
     */
    public function testGetField()
    {
        $this->initializeFieldDescription(self::TEST_NAME);

        $this->model->initialize($this->fieldDescription);
        $this->assertEquals($this->fieldDescription, $this->model->getField());
    }

    /**
     * @depends testInitialize
     */
    public function testGetName()
    {
        $this->initializeFieldDescription(self::TEST_NAME);

        $this->model->initialize($this->fieldDescription);
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    /**
     * @param mixed $direction
     * @param null $expected
     *
     * @dataProvider getDirectionsDataProvider
     */
    public function testSetDirection($direction = null, $expected = null)
    {
        $this->model->setDirection($direction);
        $this->assertAttributeEquals($expected, 'direction', $this->model);
    }

    /**
     * Data provider for testSetDirections
     *
     * @return array
     */
    public function getDirectionsDataProvider()
    {
        return array(
            'not_sorted' => array(),
            'sorted_by_asc' => array(
                '$direction' => self::TEST_ASC_DIRECTION,
                '$expected'  => self::TEST_ASC_DIRECTION
            ),
            'sorted_by_desc' => array(
                '$direction' => self::TEST_DESC_DIRECTION,
                '$expected'  => self::TEST_DESC_DIRECTION
            ),
            'sorted_using_true_value' => array(
                '$direction' => true,
                '$expected'  => self::TEST_DESC_DIRECTION
            ),
            'sorted_using_false_value' => array(
                '$direction' => false,
                '$expected'  => self::TEST_ASC_DIRECTION
            )
        );
    }

    /**
     * @depends testSetDirection
     */
    public function testGetDirection()
    {
        $this->model->setDirection(self::TEST_ASC_DIRECTION);
        $this->assertEquals(self::TEST_ASC_DIRECTION, $this->model->getDirection());
    }

    /**
     * @depends testInitialize
     * @depends testSetDirection
     * @depends testGetDirection
     *
     * @dataProvider getFieldOptionsDataProvider
     */
    public function testApply($fieldName, $fieldOptions, $direction, $expectedFieldName)
    {
        $this->initializeFieldDescription($fieldName, $fieldOptions);

        $this->model->initialize($this->fieldDescription);

        $queryBuilderMock = $this->getMock('Doctrine\ORM\QueryBuilder', array('addOrderBy'), array(), '', false);
        $queryBuilderMock->expects($this->once())
            ->method('addOrderBy')
            ->with($expectedFieldName, $direction);

        $proxyQueryMock = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery', array('getQueryBuilder', 'entityJoin'), array(), '', false);
        $proxyQueryMock->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($queryBuilderMock));
        $proxyQueryMock->expects($this->any())
            ->method('entityJoin')
            ->will($this->returnCallback(array($this, 'entityJoin')));

        $this->model->apply($proxyQueryMock, $direction);
    }

    /**
     * @param array $associationMapping
     * @return string
     */
    public function entityJoin(array $associationMapping)
    {
        $alias = 'main';
        if ($associationMapping) {
            $alias = array_shift($associationMapping);
        }

        return $alias;
    }

    /**
     * Data provider for testApply
     *
     * @return array
     */
    public function getFieldOptionsDataProvider()
    {
        return array(
            'sort_by_complex_field' => array(
                '$fieldName'         => self::TEST_NAME,
                '$fieldOptions'      => array('complex_data' => true),
                '$direction'         => self::TEST_ASC_DIRECTION,
                '$expectedFieldName' => self::TEST_NAME
            ),
            'sort_by_field_with_alias' => array(
                '$fieldName'         => self::TEST_NAME,
                '$fieldOptions'      => array('entity_alias' => self::TEST_ALIAS),
                '$direction'         => self::TEST_ASC_DIRECTION,
                '$expectedFieldName' => self::TEST_ALIAS.'.'.self::TEST_NAME
            ),
            'sort_by_field_with_alias_mapping' => array(
                '$fieldName'         => self::TEST_NAME,
                '$fieldOptions'      => array('parent_association_mappings' => array(self::TEST_ALIAS)),
                '$direction'         => self::TEST_ASC_DIRECTION,
                '$expectedFieldName' => self::TEST_ALIAS.'.'.self::TEST_NAME
            ),
            'sort_order_by_predefined_direction' => array(
                '$fieldName'         => self::TEST_NAME,
                '$fieldOptions'      => array('parent_association_mappings' => array(self::TEST_ALIAS)),
                '$direction'         => null,
                '$expectedFieldName' => self::TEST_ALIAS.'.'.self::TEST_NAME
            )
        );
    }
}
