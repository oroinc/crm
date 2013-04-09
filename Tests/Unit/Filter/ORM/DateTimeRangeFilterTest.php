<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\DateTimeRangeFilter;
use Oro\Bundle\GridBundle\Form\Type\Filter\DateTimeRangeType;

class DateTimeRangeFilterTest extends FilterTestCase
{
    /**
     * @var array
     */
    protected $filterTypes = array(DateTimeRangeType::TYPE_BETWEEN, DateTimeRangeType::TYPE_NOT_BETWEEN);

    /**
     * @var DateTimeRangeFilter
     */
    protected $model;

    protected function setUp()
    {
        $translator = $this->getTranslatorMock();
        $this->model = new DateTimeRangeFilter($translator);
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testProperties()
    {
        $this->assertAttributeEquals(true, 'time', $this->model);
    }

    public function testGetTypeOptions()
    {
        $actualTypes = $this->model->getTypeOptions();
        $this->assertTypeOptions($actualTypes);
    }

    /**
     * Test only element name, other logic is already tested in AbstractDateFilterTest
     */
    public function testGetRenderSettings()
    {
        $renderSettings = $this->model->getRenderSettings();
        $this->assertEquals('oro_grid_type_filter_datetime_range', $renderSettings[0]);
    }
}
