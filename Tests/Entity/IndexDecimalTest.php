<?php
namespace Oro\Bundle\SearchBundle\Test\Entity;

use Oro\Bundle\SearchBundle\Entity\IndexDecimal;

class IndexDecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\IndexDecimal
     */
    private $index;

    public function setUp()
    {
        $this->index = new IndexDecimal();
    }

    public function testField()
    {
        $this->assertNull($this->index->getField());
        $this->index->setField('test_decimal_field');
        $this->assertEquals('test_decimal_field', $this->index->getField());
    }

    public function testValue()
    {
        $this->assertNull($this->index->getValue());
        $this->index->setValue(55.25);
        $this->assertEquals(55.25, $this->index->getValue());
    }
}
