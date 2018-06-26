<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Method;

class MethodTest extends \PHPUnit\Framework\TestCase
{
    public function testName()
    {
        $obj = new Method('test');
        $this->assertEquals('test', $obj->getName());
    }

    public function testLabel()
    {
        $obj = new Method('test');
        $obj->setLabel('TEST_LABEL');
        $this->assertEquals('TEST_LABEL', $obj->getLabel());
        $this->assertEquals('TEST_LABEL', $obj->__toString());
    }
}
