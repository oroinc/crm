<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Field;

use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Tests\Unit\Field\Stub\StubEntity;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

class FieldDescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_NAME       = 'test_name';
    const TEST_FULL_NAME  = 'test.field_name';
    const TEST_FIELD_NAME = 'field_name';
    const TEST_TEMPLATE   = 'test_template';
    const TEST_TYPE       = 'test_type';
    const TEST_ID_FIELD   = 'test_id_field';
    const TEST_LABEL      = 'test_label';

    const TEST_OPTION_NAME    = 'test_option_name';
    const TEST_OPTION_VALUE   = 'test_option_value';
    const TEST_DEFAULT_VALUE  = 'test_default_value';
    const TEST_SPECIFIC_VALUE = 'test_specific_value';

    const TEST_TARGET_ENTITY = 'test_target_entity';
    const TEST_MAPPING_TYPE  = 'test_mapping_type';

    /**
     * @var FieldDescription
     */
    protected $model;

    /**
     * @var array
     */
    protected $testOptions = array(
        'option_1'             => 'value_1',
        'option_2'             => 'value_2',
        self::TEST_FIELD_NAME  => self::TEST_DEFAULT_VALUE,
        self::TEST_OPTION_NAME => array(
            self::TEST_OPTION_NAME => self::TEST_OPTION_VALUE,
            self::TEST_FIELD_NAME  => self::TEST_DEFAULT_VALUE
        )
    );

    /**
     * @var array
     */
    protected $testAssociationMapping = array(
        'targetEntity' => self::TEST_TARGET_ENTITY
    );

    protected $testFieldMapping = array(
        'id'   => self::TEST_ID_FIELD,
        'name' => self::TEST_FIELD_NAME
    );

    protected function setUp()
    {
        $this->model = new FieldDescription();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetFieldName()
    {
        $this->model->setFieldName(self::TEST_FULL_NAME);
        $this->assertAttributeEquals(self::TEST_FULL_NAME, 'fieldName', $this->model);
    }

    public function testGetFieldName()
    {
        $this->model->setFieldName(self::TEST_FULL_NAME);
        $this->assertEquals(self::TEST_FULL_NAME, $this->model->getFieldName());
    }

    public function testSetNameWithFieldName()
    {
        $this->model->setFieldName(self::TEST_FULL_NAME);
        $this->model->setName(self::TEST_NAME);

        $this->assertAttributeEquals(self::TEST_NAME, 'name', $this->model);
        $this->assertEquals(self::TEST_FULL_NAME, $this->model->getFieldName());
    }

    public function testSetNameWithoutFieldName()
    {
        $this->model->setName(self::TEST_FULL_NAME);

        $this->assertAttributeEquals(self::TEST_FULL_NAME, 'name', $this->model);
        $this->assertEquals(self::TEST_FIELD_NAME, $this->model->getFieldName());
    }

    public function testGetName()
    {
        $this->model->setName(self::TEST_NAME);
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    public function testSetOption()
    {
        $this->assertAttributeEmpty('options', $this->model);
        $this->model->setOption(self::TEST_OPTION_NAME, self::TEST_OPTION_VALUE);
        $this->assertAttributeEquals(
            array(self::TEST_OPTION_NAME => self::TEST_OPTION_VALUE),
            'options',
            $this->model
        );
    }

    public function testGetOption()
    {
        $this->assertEquals(
            self::TEST_DEFAULT_VALUE,
            $this->model->getOption(self::TEST_OPTION_NAME, self::TEST_DEFAULT_VALUE)
        );

        $this->model->setOption(self::TEST_OPTION_NAME, self::TEST_OPTION_VALUE);
        $this->assertEquals(
            self::TEST_OPTION_VALUE,
            $this->model->getOption(self::TEST_OPTION_NAME, self::TEST_DEFAULT_VALUE)
        );
    }

    public function testGetOptions()
    {
        $this->model->setOption(self::TEST_OPTION_NAME, self::TEST_OPTION_VALUE);
        $this->assertEquals(
            array(self::TEST_OPTION_NAME => self::TEST_OPTION_VALUE),
            $this->model->getOptions()
        );
    }

    public function testSetTemplate()
    {
        $this->model->setTemplate(self::TEST_TEMPLATE);
        $this->assertAttributeEquals(self::TEST_TEMPLATE, 'template', $this->model);
    }

    public function testGetTemplate()
    {
        $this->model->setTemplate(self::TEST_TEMPLATE);
        $this->assertEquals(self::TEST_TEMPLATE, $this->model->getTemplate());
    }

    public function testSetType()
    {
        $this->model->setType(self::TEST_TYPE);
        $this->assertAttributeEquals(self::TEST_TYPE, 'type', $this->model);
    }

    public function testGetType()
    {
        $this->model->setType(self::TEST_TYPE);
        $this->assertEquals(self::TEST_TYPE, $this->model->getType());
    }

    public function testSetOptions()
    {
        // some data to rewrite
        $this->model->setOption(self::TEST_OPTION_NAME, self::TEST_OPTION_VALUE);

        $options = $this->testOptions;
        $options['template'] = self::TEST_TEMPLATE;
        $options['type']     = self::TEST_TYPE;

        $this->model->setOptions($options);
        $this->assertEquals($this->testOptions, $this->model->getOptions());
        $this->assertEquals(self::TEST_TEMPLATE, $this->model->getTemplate());
        $this->assertEquals(self::TEST_TYPE, $this->model->getType());
    }

    public function testSetAssociationMapping()
    {
        $this->model->setAssociationMapping($this->testAssociationMapping);
        $this->assertAttributeEquals($this->testAssociationMapping, 'associationMapping', $this->model);
    }

    public function testGetAssociationMapping()
    {
        $this->model->setAssociationMapping($this->testAssociationMapping);
        $this->assertEquals($this->testAssociationMapping, $this->model->getAssociationMapping());
    }

    public function testSetFieldMapping()
    {
        $this->model->setFieldMapping($this->testFieldMapping);
        $this->assertAttributeEquals($this->testFieldMapping, 'fieldMapping', $this->model);
    }

    public function testGetFieldMapping()
    {
        $this->model->setFieldMapping($this->testFieldMapping);
        $this->assertEquals($this->testFieldMapping, $this->model->getFieldMapping());
    }

    public function testGetTargetEntity()
    {
        // no association mappings
        $this->assertNull($this->model->getTargetEntity());

        // correct association mappings
        $this->testSetAssociationMapping($this->testAssociationMapping);
        $this->assertEquals(self::TEST_TARGET_ENTITY, $this->model->getTargetEntity());
    }

    public function testIsIdentifier()
    {
        // no field mappings
        $this->assertFalse($this->model->isIdentifier());

        // field mappings with identifier
        $this->model->setFieldMapping($this->testFieldMapping);
        $this->assertTrue($this->model->isIdentifier());
    }

    public function testMergeOption()
    {
        // new option
        $this->model->mergeOption(self::TEST_OPTION_NAME, $this->testOptions[self::TEST_OPTION_NAME]);
        $this->assertEquals(
            array(self::TEST_OPTION_NAME => $this->testOptions[self::TEST_OPTION_NAME]),
            $this->model->getOptions()
        );

        // replace option
        $this->model->setOptions($this->testOptions);
        $this->assertEquals(
            $this->testOptions[self::TEST_OPTION_NAME],
            $this->model->getOption(self::TEST_OPTION_NAME)
        );

        $newOption = array(self::TEST_FIELD_NAME => self::TEST_SPECIFIC_VALUE);
        $this->model->mergeOption(self::TEST_OPTION_NAME, $newOption);

        $expectedOption = array_merge($this->testOptions[self::TEST_OPTION_NAME], $newOption);
        $this->assertEquals(
            $expectedOption,
            $this->model->getOption(self::TEST_OPTION_NAME)
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The key "field_name" does not point to an array value
     */
    public function testMergeOptionIncorrectFormat()
    {
        $this->model->setOptions($this->testOptions);
        $this->model->mergeOption(self::TEST_FIELD_NAME, array());
    }

    public function testMergeOptions()
    {
        $newOptions = array(
            self::TEST_OPTION_NAME => array(
                self::TEST_FIELD_NAME => self::TEST_SPECIFIC_VALUE
            )
        );

        $this->model->setOptions($this->testOptions);
        $this->model->mergeOptions($newOptions);

        $expectedOptions = $this->testOptions;
        $expectedOptions[self::TEST_OPTION_NAME][self::TEST_FIELD_NAME] = array(
            self::TEST_DEFAULT_VALUE,
            self::TEST_SPECIFIC_VALUE
        );
        $this->assertEquals($expectedOptions, $this->model->getOptions());
    }

    public function testSetMappingType()
    {
        $this->model->setMappingType(self::TEST_MAPPING_TYPE);
        $this->assertAttributeEquals(self::TEST_MAPPING_TYPE, 'mappingType', $this->model);
    }

    public function testGetMappingType()
    {
        $this->model->setMappingType(self::TEST_MAPPING_TYPE);
        $this->assertEquals(self::TEST_MAPPING_TYPE, $this->model->getMappingType());
    }

    public function testGetLabel()
    {
        $this->assertNull($this->model->getLabel());
        $this->model->setOption('label', self::TEST_LABEL);
        $this->assertEquals(self::TEST_LABEL, $this->model->getLabel());
    }

    public function testIsSortable()
    {
        $this->assertFalse($this->model->isSortable());
        $this->model->setOption('sortable', true);
        $this->assertTrue($this->model->isSortable());
    }

    public function testIsFilterable()
    {
        $this->assertFalse($this->model->isFilterable());
        $this->model->setOption('filterable', true);
        $this->assertTrue($this->model->isFilterable());
    }

    public function testGetSortFieldMapping()
    {
        $this->assertNull($this->model->getSortFieldMapping());
        $this->model->setOption('sort_field_mapping', self::TEST_MAPPING_TYPE);
        $this->assertEquals(self::TEST_MAPPING_TYPE, $this->model->getSortFieldMapping());
    }

    /**
     * Data provider for testGetFieldValue
     *
     * @return array
     */
    public function getFieldValueDataProvider()
    {
        return array(
            'array_correct_data' => array(
                '$expected' => self::TEST_SPECIFIC_VALUE,
                '$object'   => array(self::TEST_FIELD_NAME => self::TEST_SPECIFIC_VALUE),
                '$name'     => self::TEST_FIELD_NAME,
            ),
            'array_null_data' => array(
                '$expected' => null,
                '$object'   => array(),
                '$name'     => self::TEST_FIELD_NAME
            ),
            'object_public_decimal' => array(
                '$expected' => 4.2,
                '$object'   => new StubEntity('4.2'),
                '$name'     => StubEntity::PUBLIC_PROPERTY_NAME,
                '$options'  => array('type' => AbstractAttributeType::BACKEND_TYPE_DECIMAL),
            ),
            'object_public_integer' => array(
                '$expected' => 42,
                '$object'   => new StubEntity('42'),
                '$name'     => StubEntity::PUBLIC_PROPERTY_NAME,
                '$options'  => array('type' => AbstractAttributeType::BACKEND_TYPE_INTEGER),
            ),
            'object_public_string' => array(
                '$expected' => self::TEST_FIELD_NAME,
                '$object'   => new StubEntity(self::TEST_FIELD_NAME),
                '$name'     => StubEntity::PUBLIC_PROPERTY_NAME,
            ),
            'object_method_by_code' => array(
                '$expected' => StubEntity::CODE_METHOD_RESULT,
                '$object'   => new StubEntity(),
                '$name'     => null,
                '$options'  => array('code' => StubEntity::CODE_METHOD_NAME),
            ),
            'object_method_by_code_null_result' => array(
                '$expected' => null,
                '$object'   => new StubEntity(),
                '$name'     => null,
                '$options'  => array('code' => StubEntity::CODE_METHOD_NAME_NULL),
            ),
            'object_method_by_getter' => array(
                '$expected' => StubEntity::GETTER_PROPERTY_RESULT,
                '$object'   => new StubEntity(),
                '$name'     => StubEntity::GETTER_PROPERTY_NAME,
            ),
            'object_method_by_checker' => array(
                '$expected' => StubEntity::CHECKER_PROPERTY_RESULT,
                '$object'   => new StubEntity(),
                '$name'     => StubEntity::CHECKER_PROPERTY_NAME,
            )
        );
    }

    /**
     * @param mixed $expected
     * @param mixed $object
     * @param string $name
     * @param array $options
     *
     * @dataProvider getFieldValueDataProvider
     */
    public function testGetFieldValue($expected, $object, $name, array $options = array())
    {
        $this->model->setName($name);
        $this->model->setOptions($options);
        $fieldValue = $this->model->getFieldValue($object);
        $this->assertEquals($expected, $fieldValue);

        switch ($this->model->getType()) {
            case AbstractAttributeType::BACKEND_TYPE_DECIMAL:
                $this->assertInternalType('float', $fieldValue);
                break;
            case AbstractAttributeType::BACKEND_TYPE_INTEGER:
                $this->assertInternalType('int', $fieldValue);
                break;
        }
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to retrieve the value of "not_existing_field"
     */
    public function testGetFieldValueNoData()
    {
        $this->model->setName('not_existing_field');
        $this->model->getFieldValue(new StubEntity());
    }
}
