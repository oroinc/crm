<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Property\FixedProperty;
use Oro\Bundle\GridBundle\Tests\Unit\Property\Stub\StubEntity;

class FixedPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to retrieve the value of "not_existing_field"
     */
    public function testGetFieldValueNoData()
    {
        $fieldProperty = new FixedProperty('not_existing_field');
        $fieldProperty->getValue(new StubEntity());
    }

    public function testGetName()
    {
        $property = new FixedProperty('name');
        $this->assertEquals('name', $property->getName());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($expectedValue, $data, $name)
    {
        $property = new FixedProperty($name);
        $this->assertSame($expectedValue, $property->getValue($data));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'array property' => array(
                'value',
                array('name' => 'value'),
                'name'
            ),
            'array empty property' => array(
                null,
                array(),
                'name'
            ),
            'object public property' => array(
                'value',
                new StubEntity('value'),
                StubEntity::PUBLIC_PROPERTY_NAME
            ),
            'object method by getter' => array(
                StubEntity::GETTER_PROPERTY_RESULT,
                new StubEntity(),
                StubEntity::GETTER_PROPERTY_NAME
            ),
            'object method by checker' => array(
                StubEntity::CHECKER_PROPERTY_RESULT,
                new StubEntity(),
                StubEntity::CHECKER_PROPERTY_NAME
            )
        );
    }

    /**
     * @param string $stringValue
     * @return mixed
     */
    private function createObjectConvertableToString($stringValue)
    {
        $result = $this->getMock('FooClass', array('__toString'));
        $result->expects($this->any())->method('__toString')->will($this->returnValue($stringValue));
        return $result;
    }
}
