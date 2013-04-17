<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\DateRangeFilter;
use Oro\Bundle\GridBundle\Form\Type\Filter\DateRangeType;

class DateRangeFilterTest extends FilterTestCase
{
    /**
     * @var array
     */
    protected $filterTypes = array(DateRangeType::TYPE_BETWEEN, DateRangeType::TYPE_NOT_BETWEEN);

    /**
     * @var DateRangeFilter
     */
    protected $model;

    protected function setUp()
    {
        $this->markTestSkipped();
        $translator = $this->getTranslatorMock();
        $this->model = new DateRangeFilter($translator);
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testProperties()
    {
        $this->assertAttributeEquals(false, 'time', $this->model);
    }

    public function testGetTypeOptions()
    {
        $actualTypes = $this->model->getTypeOptions();
        $this->assertTypeOptions($actualTypes);
    }
}
