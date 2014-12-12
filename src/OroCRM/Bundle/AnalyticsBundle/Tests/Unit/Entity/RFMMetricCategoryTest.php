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
     * @param mixed $expected
     * @param mixed $default
     */
    public function testSettersAndGetters($property, $value, $expected, $default = null)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertEquals($default, $propertyAccessor->getValue($this->entity, $property));
        $propertyAccessor->setValue($this->entity, $property, $value);
        $this->assertEquals($expected, $propertyAccessor->getValue($this->entity, $property));
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
                ['channel', $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel'),
                    $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel')],
                ['owner', $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization'),
                    $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization')],
                ['category_type', RFMMetricCategory::TYPE_RECENCY, RFMMetricCategory::TYPE_RECENCY],
                ['category_index', 1, 1],
                ['minValue', 123, 123],
                ['minValue', 123.3, 123.3],
                ['minValue', '123.3', 123.3],
                ['minValue', '12', 12],
                ['minValue', '', 0],
                ['maxValue', 321, 321],
                ['maxValue', 321.3, 321.3],
                ['maxValue', '321.3', 321.3],
                ['maxValue', '', 0],
                ['maxValue', '12', 12],
                ['maxValue', null, null],
                ['minValue', null, null],
            ];
    }
}
