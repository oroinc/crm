<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Query;

use Oro\Bundle\SearchBundle\Query\Result\Item;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    protected $om;
    protected $item;
    protected $product;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroTestBundle:test'))
            ->will($this->returnValue($this->repository));

        $this->product = new Product();
        $this->product->setName('test product');

        $this->repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->product));

        $this->item = new Item($this->om, 'OroTestBundle:test', 1);
    }

    public function testGetEntityName()
    {
        $this->assertEquals('OroTestBundle:test', $this->item->getEntityName());
    }

    public function testGetRecordId()
    {
        $this->assertEquals(1, $this->item->getRecordId());
    }

    public function testGetEntity()
    {
        $this->om->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('OroTestBundle:test'))
            ->will($this->returnValue($this->repository));

        $this->item->getEntity();
    }
}
