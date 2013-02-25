<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Entity;

use Oro\Bundle\SearchBundle\Entity\Item;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\SearchBundle\Entity\Item
     */
    private $item;

    public function setUp()
    {
        $this->item = new Item();
    }

    public function testRecordId()
    {
        $this->assertNull($this->item->getRecordId());
        $this->item->setRecordId(2);
        $this->assertEquals(2, $this->item->getRecordId());
    }

    public function testChanged()
    {
        $this->assertEquals(false, $this->item->getChanged());
        $this->item->setChanged(true);
        $this->assertEquals(true, $this->item->getChanged());
    }

    public function testCreatedAt()
    {
        $this->assertNull($this->item->getCreatedAt());
        $this->item->setCreatedAt(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->item->getCreatedAt()->format('Y-m-d'));
    }

    public function testUpdatedAt()
    {
        $this->assertNull($this->item->getUpdatedAt());
        $this->item->setUpdatedAt(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->item->getUpdatedAt()->format('Y-m-d'));
    }

    public function testAlias()
    {
        $this->assertNull($this->item->getAlias());
        $this->item->setAlias('test alias');
        $this->assertEquals('test alias', $this->item->getAlias());
    }
}
