<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\OpportunityCloseReason;

class OpportunityCloseReasonStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testGetName()
    {
        $obj = new OpportunityCloseReason('test_name');
        $this->assertEquals('test_name', $obj->getName());
    }

    public function testGetLabel()
    {
        $obj = new OpportunityCloseReason('test_name');
        $obj->setLabel('test_label');
        $this->assertEquals('test_label', $obj->getLabel());
    }
}
