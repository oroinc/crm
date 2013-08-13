<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Method;

class MethodTest extends \PHPUnit_Framework_TestCase
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
