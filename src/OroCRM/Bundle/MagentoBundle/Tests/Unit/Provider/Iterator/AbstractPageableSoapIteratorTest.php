<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\Stub\TestPageableSoapIterator;

class AbstractPageableSoapIteratorTest extends BaseIteratorTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|AbstractPageableSoapIterator */
    protected $iterator;

    public function testIterator()
    {
        $date     = new \DateTime('now');
        $settings = [
            'website_id'      => 1,
            'start_sync_date' => $date
        ];

        $iterator = new TestPageableSoapIterator($this->transport, $settings);

        $this->assertEquals(
            $date,
            $iterator->getStartDate()
        );
    }
}
