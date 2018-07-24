<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableSoapIterator;

class AbstractPageableSoapIteratorTest extends BaseSoapIteratorTestCase
{
    public function testIterator()
    {
        $date     = new \DateTime('now');
        $settings = [
            'website_id'      => 1,
            'start_sync_date' => $date
        ];

        /** @var \PHPUnit\Framework\MockObject\MockObject|AbstractPageableSoapIterator $iterator */
        $iterator = $this->getMockForAbstractClass(
            'Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractPageableSoapIterator',
            [$this->transport, $settings]
        );

        $this->assertEquals(
            $date,
            $iterator->getStartDate()
        );
    }
}
