<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

class OpportunityStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $obj = new OpportunityStatus('test_name');
        $this->assertEquals('test_name', $obj->getName());
    }

    public function testGetLabel()
    {
        $obj = new OpportunityStatus('test_name');
        $obj->setLabel('test_label');
        $this->assertEquals('test_label', $obj->getLabel());
    }
}
