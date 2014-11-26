<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;

class RFMMetricCategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RFMMetricCategory
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new RFMMetricCategory();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     * @param string $property
     * @param mixed $value
     * @param mixed $default
     */
    public function testSettersAndGetters($property, $value, $default = null)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertEquals($default, $propertyAccessor->getValue($this->entity, $property));
        $propertyAccessor->setValue($this->entity, $property, $value);
        $this->assertEquals($value, $propertyAccessor->getValue($this->entity, $property));
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
            ['channel', $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel')],
            ['owner', $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization')],
            ['type', RFMMetricCategory::TYPE_RECENCY],
            ['index', 1],
            ['minValue', 123],
            ['maxValue', 321],
        ];
    }
}
