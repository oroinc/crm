<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MarketingListItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListItem
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new MarketingListItem();
    }

    protected function tearDown()
    {
        unset($this->entity);
    }

    public function testGetId()
    {
        $this->assertNull($this->entity->getId());

        $value = 42;
        $idReflection = new \ReflectionProperty(get_class($this->entity), 'id');
        $idReflection->setAccessible(true);
        $idReflection->setValue($this->entity, $value);
        $this->assertEquals($value, $this->entity->getId());
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($this->entity, $property, $value);
        $this->assertEquals($value, $accessor->getValue($this->entity, $property));
    }

    public function propertiesDataProvider()
    {
        return array(
            array('entityId', 2),
            array('contactedTimes', 3),
            array('marketingList', $this->getMock('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')),
            array('lastContactedAt', new \DateTime()),
            array('createdAt', new \DateTime()),
        );
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->entity->getCreatedAt());
        $this->entity->beforeSave();
        $this->assertInstanceOf('\DateTime', $this->entity->getCreatedAt());
    }

    public function testContact()
    {
        $this->assertEmpty($this->entity->getContactedTimes());
        $this->assertEmpty($this->entity->getLastContactedAt());
        $this->entity->contact();
        $this->assertEquals(1, $this->entity->getContactedTimes());
        $this->assertInstanceOf('\DateTime', $this->entity->getLastContactedAt());
    }
}
