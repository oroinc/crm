<?php
namespace Oro\Bundle\SearchBundle\Test\Entity;

use Oro\Bundle\SearchBundle\Entity\IndexText;

class IndexTextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\IndexText
     */
    private $index;

    public function setUp()
    {
        $this->index = new IndexText();
    }

    public function testField()
    {
        $this->assertNull($this->index->getField());
        $this->index->setField('test_text_field');
        $this->assertEquals('test_text_field', $this->index->getField());
    }

    public function testValue()
    {
        $this->assertNull($this->index->getValue());
        $this->index->setValue('test_text_value');
        $this->assertEquals('test_text_value', $this->index->getValue());
    }
}
