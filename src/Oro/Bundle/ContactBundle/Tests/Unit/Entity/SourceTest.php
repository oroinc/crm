<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Source;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    public function testName(): void
    {
        $obj = new Source('test');
        $this->assertEquals('test', $obj->getName());
    }

    public function testLabel(): void
    {
        $obj = new Source('test');
        $obj->setLabel('TEST_LABEL');
        $this->assertEquals('TEST_LABEL', $obj->getLabel());
        $this->assertEquals('TEST_LABEL', $obj->__toString());
    }
}
