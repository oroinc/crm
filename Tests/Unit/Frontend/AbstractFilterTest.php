<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Frontend;

use Oro\Bundle\FilterBundle\Frontend\AbstractFilter;

class FieldDescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    protected function setUp()
    {
        $this->filter = $this->getMockBuilder('Oro\Bundle\FilterBundle\Frontend\AbstractFilter')
            ->getMockForAbstractClass();
    }

    public function testSetName()
    {
        $name = 'test';
        $this->filter->setName($name);
        $this->assertAttributeEquals($name, 'name', $this->filter);
    }

    public function testGetName()
    {
        $name = 'test';
        $this->filter->setName($name);
        $this->assertEquals($name, $this->filter->getName());
    }

    public function testIsValid()
    {
        $this->assertTrue($this->filter->isValid('test'));
    }

    public function testParseValue()
    {
        $value = 'test';
        $this->assertEquals($value, $this->filter->parseValue($value));
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(array(), $this->filter->getDefaultOptions());
    }

    /**
     * @dataProvider setOptionsDataProvider
     * @param array $options
     * @param array $defaultOptions
     * @param array $expectedOptions
     */
    public function testSetOptions(array $options, array $defaultOptions, array $expectedOptions)
    {
        $filter = $this->getMockBuilder('Oro\Bundle\FilterBundle\Frontend\AbstractFilter')
            ->setMethods(array('getDefaultOptions'))
            ->getMockForAbstractClass();

        $filter->expects($this->once())->method('getDefaultOptions')->will($this->returnValue($defaultOptions));
        $filter->setOptions($options);
        $this->assertAttributeEquals($expectedOptions, 'options', $filter);
    }

    public function setOptionsDataProvider()
    {
        return array(
            array(
                'options' => array('foo' => 1, 'bar' => 2),
                'defaultOptions' => array(),
                'expectedOptions' => array('foo' => 1, 'bar' => 2)
            ),
            array(
                'options' => array('foo' => 3, 'bar' => 4),
                'defaultOptions' => array('foo' => 1, 'baz' => 2),
                'expectedOptions' => array('foo' => 3, 'baz' => 2, 'bar' => 4)
            ),
            array(
                'options' => array(),
                'defaultOptions' => array('foo' => 1, 'bar' => 2),
                'expectedOptions' => array('foo' => 1, 'bar' => 2)
            )
        );
    }

    public function testGetOptions()
    {
        $defaultOptions = array('foo' => 1, 'bar' => 2);
        $filter = $this->getMockBuilder('Oro\Bundle\FilterBundle\Frontend\AbstractFilter')
            ->setMethods(array('getDefaultOptions'))
            ->getMockForAbstractClass();

        $filter->expects($this->once())->method('getDefaultOptions')->will($this->returnValue($defaultOptions));
        $this->assertEquals($defaultOptions, $filter->getOptions());
        // another call get options will not call getDefaultOptions
        $this->assertEquals($defaultOptions, $filter->getOptions());
    }

    public function testAddOptions()
    {
        $expectedOptions = $addOptions = array('foo' => 1);
        $this->filter->addOptions($addOptions);
        $this->assertEquals($expectedOptions, $this->filter->getOptions());

        $addOptions = array('bar' => 2);
        $this->filter->addOptions($addOptions);
        $expectedOptions = array('foo' => 1, 'bar' => 2);
        $this->assertEquals($expectedOptions, $this->filter->getOptions());

        $addOptions = array('bar' => 3, 'baz' => 4);
        $this->filter->addOptions($addOptions);
        $expectedOptions = array('foo' => 1, 'bar' => 3, 'baz' => 4);
        $this->assertEquals($expectedOptions, $this->filter->getOptions());
    }
}
