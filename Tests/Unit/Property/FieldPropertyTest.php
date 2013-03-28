<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Tests\Unit\Property\Stub\StubEntity;

class FieldPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unable to retrieve the value of "not_existing_field"
     */
    public function testGetValueNoData()
    {
        $fieldProperty = new FieldProperty($this->createFieldDescription('not_existing_field'));
        $fieldProperty->getValue(new StubEntity());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($expectedValue, $data, FieldDescription $fieldDescription)
    {
        $fieldProperty = new FieldProperty($fieldDescription);
        $this->assertSame($expectedValue, $fieldProperty->getValue($data));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'default field type' => array(
                'value',
                array(
                    'field' => 'value',
                ),
                $this->createFieldDescription('field')
            ),
            'text field type' => array(
                'text',
                array(
                    'fieldText' => 'text',
                ),
                $this->createFieldDescription('fieldText', array('type' => FieldDescription::TYPE_TEXT))
            ),
            'decimal field type' => array(
                100.0,
                array(
                    'fieldDecimal' => '100.0',
                ),
                $this->createFieldDescription('fieldDecimal', array('type' => FieldDescription::TYPE_DECIMAL))
            ),
            'integer field type' => array(
                100,
                array(
                    'fieldInteger' => 100.0,
                ),
                $this->createFieldDescription('fieldInteger', array('type' => FieldDescription::TYPE_INTEGER))
            ),
            'date field type' => array(
                '2013-01-01T13:00:00+0200',
                array(
                    'dateField' => new \DateTime('2013-01-01 13:00:00+0200'),
                ),
                $this->createFieldDescription('dateField', array('type' => FieldDescription::TYPE_DATE))
            ),
            'datetime field type' => array(
                '2013-01-01T15:00:00+0200',
                array(
                    'datetimeField' => new \DateTime('2013-01-01 15:00:00+0200'),
                ),
                $this->createFieldDescription('datetimeField', array('type' => FieldDescription::TYPE_DATETIME))
            ),
            'not datetime value in field type' => array(
                '2013-01-01T17:00:00+0200',
                array(
                    'datetimeField' => '2013-01-01T17:00:00+0200',
                ),
                $this->createFieldDescription('datetimeField', array('type' => FieldDescription::TYPE_DATE))
            ),
            'object with __toString' => array(
                'string value',
                array(
                    'fieldName' => $this->createObjectConvertableToString('string value'),
                ),
                $this->createFieldDescription('fieldName')
            ),
            'object method by code' => array(
                StubEntity::CODE_METHOD_RESULT,
                new StubEntity(),
                $this->createFieldDescription(null, array('code' => StubEntity::CODE_METHOD_NAME))
            ),
            'object method by code with null result' => array(
                null,
                new StubEntity(),
                $this->createFieldDescription(null, array('code' => StubEntity::CODE_METHOD_NAME_NULL))
            ),
        );
    }

    /**
     * @param string $name
     * @param array $options
     * @return FieldDescription
     */
    private function createFieldDescription($name, array $options = array())
    {
        $result = new FieldDescription();
        $result->setName($name);
        $result->setOptions($options);
        return $result;
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
