<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\LeadStatus;

class LeadStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $obj = new LeadStatus('test_name');
        $this->assertEquals('test_name', $obj->getName());
    }

    public function testGetLabel()
    {
        $obj = new LeadStatus('test_name');
        $obj->setLabel('test_label');
        $this->assertEquals('test_label', $obj->getLabel());
    }
}
