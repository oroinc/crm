<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator;

class AbstractPageableSoapIteratorTest extends BaseIteratorTestCase
{
    public function testIterator()
    {
        $date     = new \DateTime('now');
        $settings = [
            'website_id'      => 1,
            'start_sync_date' => $date
        ];

        /** @var \PHPUnit_Framework_MockObject_MockObject|AbstractPageableSoapIterator $iterator */
        $iterator = $this->getMockForAbstractClass(
            'OroCRM\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator',
            [$this->transport, $settings]
        );

        $this->assertEquals(
            $date,
            $iterator->getStartDate()
        );
    }
}
