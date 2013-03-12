<?php
namespace Oro\Bundle\SegmentationTreeBundle\Tests\Unit\Model;

/**
 * Tests on AbstractSegment
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AbstractSegmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractSegment $segment
     */
    protected $segment;

    public function setUp()
    {
        $this->segment = $this->getMockForAbstractClass("Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment");
    }

    public function testGetId()
    {
        $this->assertNull($this->segment->getId());
    } 

    public function testGetTitle()
    {
        $title = "my title";
        $this->segment->setTitle($title);
        $this->assertEquals($title,$this->segment->getTitle());
    }

    public function testGetLeft()
    {
        $left = "8";
        $this->segment->setLeft($left);
        $this->assertEquals($left, $this->segment->getLeft());
    }

    public function testGetLevel()
    {
        $level = "5";
        $this->segment->setLevel($level);
        $this->assertEquals($level, $this->segment->getLevel());
    }

    public function testGetRight()
    {
        $right = "3";
        $this->segment->setRight($right);
        $this->assertEquals($right, $this->segment->getRight());
    }

    public function testGetRoot()
    {
        $root = "9";
        $this->segment->setRoot($root);
        $this->assertEquals($root, $this->segment->getRoot());
    }

    public function testGetParent()
    {
        $parentSegment = $this->getMockForAbstractClass("Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment");
        $this->segment->setParent($parentSegment);
        $this->assertEquals($parentSegment, $this->segment->getParent());
    }

    public function testAddChild()
    {
        $childSegment = $this->getMockForAbstractClass("Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment");
        $this->segment->addChild($childSegment);
        $children = $this->segment->getChildren();
        $this->assertEquals($childSegment, $children[0]);
    }

    public function testHasChildren()
    {
        $this->assertFalse($this->segment->hasChildren());
        $childSegment = $this->getMockForAbstractClass("Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment");
        $this->segment->addChild($childSegment);
        $this->assertTrue($this->segment->hasChildren());
    }

    public function testRemoveChild()
    {
        $childSegment = $this->getMockForAbstractClass("Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment");
        $this->segment->addChild($childSegment);
        $this->assertTrue($this->segment->hasChildren());
        $this->segment->removeChild($childSegment);
        $this->assertFalse($this->segment->hasChildren());
    }

    
}
