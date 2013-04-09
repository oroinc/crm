<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class AbstractFilterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME           = 'test_name';
    const TEST_VALUE          = 'test_value';
    const TEST_TYPE           = 'test_type';
    const TEST_FIELD_NAME     = 'test_field_name';
    const TEST_ALIAS_BASIC    = 'test_basic_alias';
    const TEST_ALIAS_MAPPING  = 'test_mapping_alias';
    const TEST_PARENT_MAPPING = 'test_parent_mapping';
    /**#@-*/

    /**
     * @var AbstractFilter
     */
    protected $model;

    protected function setUp()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->model = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\ORM\AbstractFilter',
            array($translator),
            '',
            true,
            true,
            true,
            array('filter', 'getDefaultOptions')
        );
        $this->model->expects($this->any())
            ->method('getDefaultOptions')
            ->will($this->returnValue(array()));
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * Data provider for testApply
     *
     * @return array
     */
    public function applyDataProvider()
    {
        return array(
            'no_entity_alias' => array(
                '$value'   => self::TEST_VALUE,
                '$options' => array(
                    'field_name'                  => self::TEST_FIELD_NAME,
                    'field_mapping'               => array('entityAlias' => null),
                    'parent_association_mappings' => array()
                ),
                '$expected' => array(
                    'value' => self::TEST_VALUE,
                    'alias' => self::TEST_ALIAS_BASIC,
                    'field' => self::TEST_FIELD_NAME
                )
            ),
            'with_entity_alias' => array(
                '$value'   => self::TEST_VALUE,
                '$options' => array(
                    'field_name'                  => self::TEST_FIELD_NAME,
                    'field_mapping'               => array('entityAlias' => self::TEST_ALIAS_MAPPING),
                    'parent_association_mappings' => array('mapping' => self::TEST_PARENT_MAPPING)
                ),
                '$expected' => array(
                    'value' => self::TEST_VALUE,
                    'alias' => self::TEST_ALIAS_MAPPING,
                    'field' => self::TEST_FIELD_NAME
                )
            )
        );
    }

    /**
     * @param string $value
     * @param array $options
     * @param array $expected
     *
     * @dataProvider applyDataProvider
     */
    public function testApply($value, array $options, array $expected)
    {
        $proxyQuery = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface',
            array(),
            '',
            false,
            true,
            true,
            array('entityJoin')
        );
        $proxyQuery->expects($this->once())
            ->method('entityJoin')
            ->with($options['parent_association_mappings'])
            ->will($this->returnValue(self::TEST_ALIAS_BASIC));

        $this->model->initialize(self::TEST_NAME, $options);
        $this->model->expects($this->once())
            ->method('filter')
            ->with($proxyQuery, $expected['alias'], $expected['field'], $expected['value']);

        $this->model->apply($proxyQuery, $value);

        $this->assertEquals($expected['value'], $this->model->getValue());
    }

    /**
     * Data provider for testGetFieldType
     *
     * @return array
     */
    public function getFieldTypeDataProvider()
    {
        return array(
            'no_type_option' => array(
                '$options'      => array(),
                '$expectedType' => FieldDescriptionInterface::TYPE_TEXT,
            ),
            'type_option' => array(
                '$options'      => array('type' => self::TEST_TYPE),
                '$expectedType' => self::TEST_TYPE,
            ),
        );
    }

    /**
     * @param array $options
     * @param string $expectedType
     *
     * @dataProvider getFieldTypeDataProvider
     */
    public function testGetFieldType(array $options, $expectedType)
    {
        $this->model->initialize(self::TEST_NAME, $options);
        $this->assertEquals($expectedType, $this->model->getFieldType());
    }

    public function testGetTypeOptions()
    {
        $typeOptions = $this->model->getTypeOptions();
        $this->assertInternalType('array', $typeOptions);
        $this->assertEmpty($typeOptions);
    }
}
