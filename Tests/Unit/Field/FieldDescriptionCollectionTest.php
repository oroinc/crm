<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Field;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;

class FieldDescriptionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_FIELD_NAME   = 'test_field_name';
    const TEST_FIRST_FIELD  = 'test_first_field';
    const TEST_SECOND_FIELD = 'test_second_field';
    const TEST_THIRD_FIELD  = 'test_third_field';

    /**
     * @var FieldDescriptionCollection
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new FieldDescriptionCollection();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param string $fieldName
     * @return FieldDescription
     */
    protected function getTestField($fieldName = null)
    {
        if (!$fieldName) {
            $fieldName = self::TEST_FIELD_NAME;
        }

        $field = new FieldDescription();
        $field->setName($fieldName);

        return $field;
    }

    public function testAdd()
    {
        $this->assertAttributeEmpty('elements', $this->model);
        $field = $this->getTestField();
        $this->model->add($field);
        $this->assertAttributeEquals(array(self::TEST_FIELD_NAME => $field), 'elements', $this->model);
    }

    public function testGetElements()
    {
        $this->assertEmpty($this->model->getElements());
        $field = $this->getTestField();
        $this->model->add($field);
        $this->assertEquals(array(self::TEST_FIELD_NAME => $field), $this->model->getElements());
    }

    public function testHas()
    {
        $this->assertFalse($this->model->has(self::TEST_FIELD_NAME));
        $this->model->add($this->getTestField());
        $this->assertTrue($this->model->has(self::TEST_FIELD_NAME));
    }

    public function testGet()
    {
        $field = $this->getTestField();
        $this->model->add($field);
        $this->assertEquals($field, $this->model->get(self::TEST_FIELD_NAME));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Element "test_field_name" does not exist
     */
    public function testGetNoElement()
    {
        $this->assertEmpty($this->model->getElements());
        $this->model->get(self::TEST_FIELD_NAME);
    }

    public function testRemove()
    {
        $this->model->add($this->getTestField());
        $this->assertTrue($this->model->has(self::TEST_FIELD_NAME));
        $this->model->remove(self::TEST_FIELD_NAME);
        $this->assertFalse($this->model->has(self::TEST_FIELD_NAME));
    }

    public function testOffsetExists()
    {
        $this->assertFalse($this->model->offsetExists(self::TEST_FIELD_NAME));
        $this->model->add($this->getTestField());
        $this->assertTrue($this->model->offsetExists(self::TEST_FIELD_NAME));
    }

    public function testOffsetGet()
    {
        $field = $this->getTestField();
        $this->model->add($field);
        $this->assertEquals($field, $this->model->offsetGet(self::TEST_FIELD_NAME));
    }

    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage Cannot set value, use add
     */
    public function testOffsetSet()
    {
        $this->model->offsetSet(0, 'value');
    }

    public function testOffsetUnset()
    {
        $this->model->add($this->getTestField());
        $this->assertTrue($this->model->has(self::TEST_FIELD_NAME));
        $this->model->offsetUnset(self::TEST_FIELD_NAME);
        $this->assertFalse($this->model->has(self::TEST_FIELD_NAME));
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->model->count());
        $this->assertCount($this->model->count(), $this->model->getElements());

        $this->model->add($this->getTestField());

        $this->assertEquals(1, $this->model->count());
        $this->assertCount($this->model->count(), $this->model->getElements());
    }

    public function testReorder()
    {
        $this->model->add($this->getTestField(self::TEST_FIRST_FIELD));
        $this->model->add($this->getTestField(self::TEST_SECOND_FIELD));
        $this->model->add($this->getTestField(self::TEST_THIRD_FIELD));

        $sourceOrder   = array(self::TEST_FIRST_FIELD, self::TEST_SECOND_FIELD, self::TEST_THIRD_FIELD); // 1,2,3
        $expectedOrder = array(self::TEST_SECOND_FIELD, self::TEST_THIRD_FIELD, self::TEST_FIRST_FIELD); // 2,3,1

        $this->assertEquals($sourceOrder, array_keys($this->model->getElements()));
        $this->model->reorder($expectedOrder);
        $this->assertEquals($expectedOrder, array_keys($this->model->getElements()));
    }

    public function testGetIterator()
    {
        $this->model->add($this->getTestField());

        $iterator = $this->model->getIterator();
        $this->assertInstanceOf('\ArrayIterator', $iterator);
        $this->assertEquals($this->model->getElements(), $iterator->getArrayCopy());
    }
}
