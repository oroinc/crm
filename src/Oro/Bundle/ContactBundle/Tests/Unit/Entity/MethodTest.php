<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Method;
use PHPUnit\Framework\TestCase;

class MethodTest extends TestCase
{
    public function testName(): void
    {
        $obj = new Method('test');
        $this->assertEquals('test', $obj->getName());
    }

    public function testLabel(): void
    {
        $obj = new Method('test');
        $obj->setLabel('TEST_LABEL');
        $this->assertEquals('TEST_LABEL', $obj->getLabel());
        $this->assertEquals('TEST_LABEL', $obj->__toString());
    }
}
