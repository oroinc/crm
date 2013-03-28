<?php
namespace Oro\Bundle\SegmentationTreeBundle\DataFixtures\ORM;

use Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;

use Oro\Bundle\SegmentationTreeBundle\Entity\Item;
use Oro\Bundle\SegmentationTreeBundle\Entity\ItemSegment;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load tests items and items segment
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class LoadItemSegmentData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        // create items
        $item1 = $this->createItem('My item 1', 'A nice item (1)');
        $item2 = $this->createItem('My item 2', 'A nice item (2)');
        $item3 = $this->createItem('My item 3', 'A nice item (3)');
        $item4 = $this->createItem('My item 4', 'A nice item (4)');
        $item5 = $this->createItem('My item 5', 'A nice item (5)');

        // create trees and segments linked
        $treeRoot1 = $this->createSegment('Tree One');

        $items1 = array($item1, $item2, $item3);
        $segment1 = $this->createSegment('Segment One', $treeRoot1, $items1);

        $treeRoot2 = $this->createSegment('Tree Two');
        $segment2 = $this->createSegment('Segment Two', $treeRoot2);

        $items2 = array($item3, $item4, $item5);
        $segment3 = $this->createSegment('Segment Three', $segment2, $items2);

        $segment4 = $this->createSegment('Segment Four', $segment2);
        $segment5 = $this->createSegment('Segment Five', $segment4);
        $segment6 = $this->createSegment('Segment Six', $segment4);

        $this->manager->flush();

        // translate trees and segments
        $locale = 'fr_FR';
        $this->translate($treeRoot1, $locale, 'Arbre un');
        $this->translate($segment1, $locale, 'Segment un');

        $this->translate($treeRoot2, $locale, 'Arbre deux');
        $this->translate($segment2, $locale, 'Segment deux');

        $this->translate($segment3, $locale, 'Segment trois');
        $this->translate($segment4, $locale, 'Segment quatre');
        $this->translate($segment5, $locale, 'Segment cinq');
        $this->translate($segment6, $locale, 'Segment six');

        $this->manager->flush();
    }

    /**
     * Translate a segment
     * @param AbstractSegment $segment Segment
     * @param string          $locale  Locale used
     * @param string          $title   Title translated in locale value linked
     */
    protected function translate(AbstractSegment $segment, $locale, $title)
    {
        $segment->setTranslatableLocale($locale);
        $segment->setTitle($title);
        $this->manager->persist($segment);
    }

    /**
     * Create a Segment entity
     *
     * @param string      $title  Title of the segment
     * @param ItemSegment $parent Parent segment
     * @param array       $items  Items that should be associated to this segment
     *
     * @return ItemSegment
     */
    protected function createSegment($title, $parent = null, $items = array())
    {
        $segment = new ItemSegment();
        $segment->setTitle($title);
        $segment->setParent($parent);

        foreach ($items as $item) {
            $segment->addItem($item);
        }

        $this->manager->persist($segment);

        return $segment;
    }

    /**
     * Create a Item entity
     * @param string $name        Name of the item
     * @param string $description Description of the item
     *
     * @return Item
     */
    protected function createItem($name, $description)
    {
        $item= new Item();
        $item->setName($name);
        $item->setDescription($description);

        $this->manager->persist($item);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
