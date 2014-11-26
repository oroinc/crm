<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Entity;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;

class RFMMetricCategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RFMMetricCategory
     */
    protected $target;

    public function setUp()
    {
        $this->target = new RFMMetricCategory();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->target->$method($value);

        $this->assertInstanceOf(get_class($this->target), $result);
        $this->assertEquals($value, $this->target->{'get' . $property}());
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
            ['idx', 1],
            ['maxValue', 123],
        ];
    }
}
