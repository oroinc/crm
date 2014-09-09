<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MarketingListTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListType
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new MarketingListType(MarketingListType::TYPE_DYNAMIC);
    }

    protected function tearDown()
    {
        unset($this->entity);
    }

    public function testGetName()
    {
        $this->assertEquals(MarketingListType::TYPE_DYNAMIC, $this->entity->getName());
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
            array('label', 'test'),
        );
    }


    public function testToString()
    {
        $this->entity->setLabel('test');
        $this->assertEquals('test', $this->entity->__toString());
    }
}
