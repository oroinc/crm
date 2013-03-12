<?php
namespace Oro\Bundle\SegmentationTreeBundle\Tests\Unit\Model;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\SegmentationTreeBundle\Model\SegmentManager;
use Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment;

/**
 * Tests on SegmentManager
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class SegmentManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SegmentManager $segmentManager
     */
    protected $segmentManager;
    protected $storageManager;
    protected $entityRepository;

    const ENTITY_NAME = 'Oro\Bundle\SegmentationTreeBundle\Tests\Unit\Model\SegmentStub';
    const ENTITY_SHORT_NAME = 'SegmentStub_ShortName';

    public function setUp()
    {
        $this->entityRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->storageManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->storageManager->expects($this->any())
             ->method('getRepository')
             ->will($this->returnValue($this->entityRepository)); 
        $this->segmentManager = new SegmentManager($this->storageManager, self::ENTITY_NAME, self::ENTITY_SHORT_NAME);
    }

    public function testGetStorageManager()
    {
        $this->assertEquals($this->segmentManager->getStorageManager(), $this->storageManager);
    }

    public function testCreateSegment()
    {
        $actualClassName = get_class($this->segmentManager->createSegment());
        $this->assertEquals($actualClassName, self::ENTITY_NAME);
    }

    public function testGetSegmentName()
    {
        $this->assertEquals($this->segmentManager->getSegmentName(), self::ENTITY_NAME);
    } 

    public function testGetSegmentShortName()
    {
        $this->assertEquals($this->segmentManager->getSegmentShortName(), self::ENTITY_SHORT_NAME);
    }

    public function testGetEntityRepository()
    {
        $this->assertEquals($this->segmentManager->getEntityRepository(), $this->entityRepository);
    } 

    public function testCopyInstance()
    {
        $rootNode = $this->segmentManager->createSegment();

        $node = $this->segmentManager->createSegment();
        $node->setTitle('parent node');
        $node->setParent($rootNode);
        $rootNode->addChild($node);

        $firstChild = $this->segmentManager->createSegment();
        $firstChild->setTitle('first child');
        $firstChild->setParent($node);
        $node->addChild($firstChild);

        $secondChild = $this->segmentManager->createSegment();
        $secondChild->setTitle('second child');
        $secondChild->setParent($node);
        $node->addChild($secondChild);

        $firstGrandChild = $this->segmentManager->createSegment();
        $firstGrandChild->setTitle('first grand child');
        $firstGrandChild->setParent($secondChild);
        $secondChild->addChild($firstGrandChild);

        $nodeCopy = $this->segmentManager->copyNode($node, $rootNode);
        $this->assertEquals($node,$nodeCopy);

        $copyChildren = $nodeCopy->getChildren();
        $copyFirstChild = $copyChildren[0];
        $this->assertEquals($copyFirstChild,$firstChild);

        $copySecondChild = $copyChildren[1];
        $this->assertEquals($copySecondChild,$secondChild);

        $copyGrandChildren = $copySecondChild->getChildren();
        $copyFirstGrandChild = $copyGrandChildren[0]; 
        $this->assertEquals($copyFirstGrandChild,$firstGrandChild);
        
    }
}

class SegmentStub extends AbstractSegment {

}
