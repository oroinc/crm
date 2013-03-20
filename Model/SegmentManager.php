<?php
namespace Oro\Bundle\SegmentationTreeBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Service class to manage segments node and tree
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class SegmentManager
{
    /**
     * Storage manager
     *
     * @var ObjectManager
     */
    protected $storageManager;

    /**
     * Class name for managed segment 
     * 
     * @var string
     */
    protected $segmentName;

    /**
     * Constructor
     * 
     * @param ObjectManager $storageManager   Storage manager
     * @param String        $segmentName      Segment class name
     */
    public function __construct($storageManager, $segmentName)
    {
        $this->storageManager = $storageManager;
        $this->segmentName = $segmentName;
    }

    /**
     * Return storage manager
     *
     * @return ObjectManager
     */
    public function getStorageManager()
    {
        return $this->storageManager;
    }

    /**
     * Create segment
     *
     * @return AbstractSegment
     *
     */
    public function createSegment()
    {
        $segmentClassName = $this->getSegmentName();

        return new $segmentClassName;
    }

    /**
     * Return segment class name (mainly used in Doctrine context)
     * 
     * @return String segment class name
     */
    public function getSegmentName()
    {
        return $this->segmentName;
    }

    /**
     * Return the entity repository reponsible for the segment
     *
     * @return EntityRepository
     */
    public function getEntityRepository()
    {
        return $this->getStorageManager()->getRepository($this->getSegmentName());
    }


    /**
     * Get all children for a parent segment id
     * @param integer $parentId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId)
    {
        $entityRepository = $this->getEntityRepository();

        return $entityRepository->getChildrenFromParentId($parentId);
    }

    /**
     * Search segments by criterias
     *
     * @param array $criterias
     *
     * @return ArrayCollection
     */
    public function search($criterias)
    {
        return $this->getEntityRepository()->search($criterias);
    }

    /**
     * Remove a segment by its id
     *
     * @param integer $segmentId Id of segment to remove
     */
    public function removeById($segmentId)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);

        $this->remove($segment);
    }

    /**
     * Remove a segment object
     *
     * @param AbstractSegment $segment
     */
    protected function remove($segment)
    {
        $this->getStorageManager()->remove($segment);
        $this->getStorageManager()->flush();
    }

    /**
     * Rename a segment
     *
     * @param integer $segmentId Segment id
     * @param string  $title     New title for segment
     */
    public function rename($segmentId, $title)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);

        $segment->setTitle($title);

        $this->getStorageManager()->persist($segment);
    }


    /**
     * Move a segment to another parent
     *
     * @param integer $segmentId   Segment to move
     * @param integer $referenceId Parent segment
     */
    public function move($segmentId, $referenceId)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);
        $reference = $repo->find($referenceId);

        $segment->setParent($reference);

        $this->getStorageManager()->persist($segment);
    }

    /**
     * Copy a segment and link it to a parent
     *
     * @param integer $segmentId   Segment to copy
     * @param integer $referenceId Parent segment
     */
    public function copy($segmentId, $referenceId)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);
        $reference = $repo->find($referenceId);

        $newSegment = $this->copyNode($segment, $reference);

        $this->getStorageManager()->persist($newSegment);
    }

    /**
     * Recursive copy
     * @param AbstractSegment $segment Segment to be copied
     * @param AbstractSegment $parent  Parent segment
     *
     * @return AbstractSegment
     * FIXME: copy relationship states as well and all attributes
     */
    public function copyNode($segment, $parent)
    {
        $newSegment = $this->createSegment();
        $newSegment->setTitle($segment->getTitle());
        $newSegment->setParent($parent);

        // copy children by recursion
        foreach ($segment->getChildren() as $child) {
            $newChild = $this->copyNode($child, $newSegment);
            $newSegment->addChild($newChild);

            $this->getStorageManager()->persist($newSegment);
        }

        return $newSegment;
    }

    /**
     * Get all tree root. They are nodes without a parent node
     *
     * @return ArrayCollection The root nodes
     */
    public function getTrees()
    {
        $repo = $this->getEntityRepository();
        return $repo->getChildrenFromParentId(null);
        
    }

    /*
     * Get all segments of a tree by its root
     *
     * @param AbstractSegment $treeRoot Tree root node
     *
     * @return ArrayCollection The tree's nodes
     */
    public function getTreeSegments(AbstractSegment $treeRoot)
    {
        $repo = $this->getEntityRepository();
        $treeRootId = $treeRoot->getId();

        return $repo->findBy(array('root' => $treeRootId));
    }

    /*
     * Create a new tree by creating a its root node
     *
     * @return AbsractSegment
     */
    public function createTree() {
        $rootSegment = $this->createSegment();
        $rootSegment->setParent(null);

        return $rootSegment;
    }

    /*
     * Remove a new tree by its root segment
     *
     * @param AbstractSegment $rootNode
     */
    public function removeTree(AbstractSegment $rootSegment) {

        $rootSegment->setParent(null);
        $this->getStorageManager()->remove($rootSegment);
    }

    /*
     * Remove a new tree by its root node id
     *
     * @param int $rootSegmentId
     */
    public function removeTreeById($rootSegmentId) {
        $repo = $this->getEntityRepository();
        $rootSegment = $repo->find($rootSegmentId);

        $this->removeTree($rootSegment);

    }
}
