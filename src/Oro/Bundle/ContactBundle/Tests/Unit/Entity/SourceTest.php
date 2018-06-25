<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Source;

class SourceTest extends \PHPUnit\Framework\TestCase
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
