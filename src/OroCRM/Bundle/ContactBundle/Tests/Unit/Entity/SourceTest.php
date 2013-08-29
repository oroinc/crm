<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Source;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $obj = new Source('test');
        $this->assertEquals('test', $obj->getName());
    }

    public function testLabel()
    {
        $obj = new Source('test');
        $obj->setLabel('TEST_LABEL');
        $this->assertEquals('TEST_LABEL', $obj->getLabel());
        $this->assertEquals('TEST_LABEL', $obj->__toString());
    }
}
