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
        // add only last id filter
        $this->filter->addLastIdFilter(1);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['complex_filter']);
        $this->assertNotEmpty($filters['complex_filter'][0]);

        // add date filter in initial mode
        $this->filter->addDateFilter('created_at', 'to', new \DateTime());
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(2, $filters['complex_filter']);
        $this->assertNotEmpty($filters['complex_filter'][1]);
        $this->assertContains('created_at', $filters['complex_filter'][1]);
        $this->assertEquals('to', $filters['complex_filter'][1]['value']['key']);

        $this->filter->addDateFilter('updated_at', 'from', new \DateTime());
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(2, $filters['complex_filter']); // still should be two filters
        $this->assertContains('updated_at', $filters['complex_filter'][1]);
        $this->assertEquals('from', $filters['complex_filter'][1]['value']['key']);

        // add website filter
        $this->filter->addWebsiteFilter([1]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(3, $filters['complex_filter']);
        $this->assertContains('website_id', $filters['complex_filter'][2]);

        // add store filter
        $this->filter->addStoreFilter([1]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(4, $filters['complex_filter']);
        $this->assertContains('store_id', $filters['complex_filter'][3]);
    }

    public function testReset()
    {
        // add only last id filter
        $this->filter->addLastIdFilter(1);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['complex_filter']);

        // add date filter in initial mode
        $this->filter->addDateFilter('created_at', 'to', new \DateTime());
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(2, $filters['complex_filter']);

        // add dummy simple filter
        $this->filter->addFilter('test', ['test' => true]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['filter']);

        // reset only simple filter
        $this->filter->reset('filter');
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertArrayNotHasKey('filter', $filters);
        $this->filter->reset();
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertArrayNotHasKey('complex_filter', $filters);
        $this->assertArrayNotHasKey('filter', $filters);

        // add dummy simple filter
        $this->filter->addFilter('test', ['test' => true]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['filter']);

        $this->filter->reset(BatchFilterBag::FILTER_TYPE_SIMPLE, 'test');
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertFalse(isset($filters['filter']['test']));
    }

    /**
     * @param BatchFilterBag $bag
     *
     * @return array
     */
    protected function getAppliedFilters(BatchFilterBag $bag)
    {
        $filters = $bag->getAppliedFilters();
        $this->assertArrayHasKey('filters', $filters);
        $filters = $filters['filters'];

        return $filters;
    }
}
