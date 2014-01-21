<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;

class BatchFilterBagTest extends \PHPUnit_Framework_TestCase
{
    /** @var BatchFilterBag */
    protected $filter;

    public function setUp()
    {
        $this->filter = new BatchFilterBag();
    }

    public function testFilters()
    {
        $filters = $this->filter->getAppliedFilters();

        // test empty filters
        $this->assertCount(2, $filters, 'Two filters returned: simple and complex');
        $this->assertEmpty($filters['complex_filter']);
        $this->assertEmpty($filters['filter']);

        // add only last id filter
        $this->filter->addLastIdFilter(1);
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(1, $filters['complex_filter']);
        $this->assertNotEmpty($filters['complex_filter']['lastid']);

        // add date filter in initial mode
        $this->filter->addDateFilter(true, new \DateTime());
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(2, $filters['complex_filter']);
        $this->assertNotEmpty($filters['complex_filter']['date']);
        $this->assertContains('created_at', $filters['complex_filter']['date']);
        $this->assertEquals('to', $filters['complex_filter']['date']['value']['key']);

        // add date filter with update mode and check that it overwrite init mode date filter
        $this->filter->addDateFilter(false, new \DateTime());
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(2, $filters['complex_filter']); // still should be two filters
        $this->assertContains('updated_at', $filters['complex_filter']['date']);
        $this->assertEquals('from', $filters['complex_filter']['date']['value']['key']);

        // add website filter
        $this->filter->addWebsiteFilter([1]);
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(3, $filters['complex_filter']);
        $this->assertContains('website_id', $filters['complex_filter']['website_id']);

        // add store filter
        $this->filter->addStoreFilter([1]);
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(4, $filters['complex_filter']);
        $this->assertContains('store_id', $filters['complex_filter']['store_id']);
    }

    public function testReset()
    {
        // add only last id filter
        $this->filter->addLastIdFilter(1);
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(1, $filters['complex_filter']);

        // add date filter in initial mode
        $this->filter->addDateFilter(true, new \DateTime());
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(2, $filters['complex_filter']);

        // add dummy simple filter
        $this->filter->addFilter('test', ['test' => true]);
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(1, $filters['filter']);

        // reset only simple filter
        $this->filter->reset('filter');
        $filters = $this->filter->getAppliedFilters();
        $this->assertCount(0, $filters['filter']);
        $this->filter->reset();
        $filters = $this->filter->getAppliedFilters();
        $this->assertEmpty($filters['complex_filter']);
        $this->assertEmpty($filters['filter']);

    }
}
