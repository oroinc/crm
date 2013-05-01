<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecord;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;

use Oro\Bundle\GridBundle\Property\FieldProperty;
use Oro\Bundle\GridBundle\Field\FieldDescription;

class FieldPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $fieldDescription = $this->createFieldDescription('name');
        $property = new FieldProperty($fieldDescription);
        $this->assertEquals('name', $property->getName());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($expectedValue, ResultRecordInterface $record, FieldDescription $fieldDescription)
    {
        $fieldProperty = new FieldProperty($fieldDescription);
        $this->assertSame($expectedValue, $fieldProperty->getValue($record));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'null value' => array(
                null,
                $this->createRecord(
                    array(
                        'field' => null,
                    )
                ),
                $this->createFieldDescription('field')
            ),
            'default field type' => array(
                'value',
                $this->createRecord(
                    array(
                        'field' => 'value',
                    )
                ),
                $this->createFieldDescription('field')
            ),
            'text field type' => array(
                'text',
                $this->createRecord(
                    array(
                        'fieldText' => 'text',
                    )
                ),
                $this->createFieldDescription('fieldText', array('type' => FieldDescription::TYPE_TEXT))
            ),
            'decimal field type' => array(
                100.0,
                $this->createRecord(
                    array(
                        'fieldDecimal' => '100.0',
                    )
                ),
                $this->createFieldDescription('fieldDecimal', array('type' => FieldDescription::TYPE_DECIMAL))
            ),
            'integer field type' => array(
                100,
                $this->createRecord(
                    array(
                        'fieldInteger' => 100.0,
                    )
                ),
                $this->createFieldDescription('fieldInteger', array('type' => FieldDescription::TYPE_INTEGER))
            ),
            'date field type' => array(
                '2013-01-01T13:00:00+0200',
                $this->createRecord(
                    array(
                        'dateField' => new \DateTime('2013-01-01 13:00:00+0200'),
                    )
                ),
                $this->createFieldDescription('dateField', array('type' => FieldDescription::TYPE_DATE))
            ),
            'datetime field type' => array(
                '2013-01-01T15:00:00+0200',
                $this->createRecord(
                    array(
                        'datetimeField' => new \DateTime('2013-01-01 15:00:00+0200'),
                    )
                ),
                $this->createFieldDescription('datetimeField', array('type' => FieldDescription::TYPE_DATETIME))
            ),
            'not datetime value in field type' => array(
                '2013-01-01T17:00:00+0200',
                $this->createRecord(
                    array(
                        'datetimeField' => '2013-01-01T17:00:00+0200',
                    )
                ),
                $this->createFieldDescription('datetimeField', array('type' => FieldDescription::TYPE_DATE))
            ),
            'object with __toString' => array(
                'string value',
                $this->createRecord(
                    array(
                        'fieldName' => $this->createObjectConvertableToString('string value'),
                    )
                ),
                $this->createFieldDescription('fieldName')
            ),
        );
    }

    /**
     * @param mixed $data
     * @return ResultRecordInterface
     */
    private function createRecord($data)
    {
        return new ResultRecord($data);
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
