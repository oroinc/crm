<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\OpportunityCloseReason;
use PHPUnit\Framework\TestCase;

class OpportunityCloseReasonStatusTest extends TestCase
{
    public function testGetName(): void
    {
        $obj = new OpportunityCloseReason('test_name');
        $this->assertEquals('test_name', $obj->getName());
    }

    public function testGetLabel(): void
    {
        $obj = new OpportunityCloseReason('test_name');
        $obj->setLabel('test_label');
        $this->assertEquals('test_label', $obj->getLabel());
    }
}
