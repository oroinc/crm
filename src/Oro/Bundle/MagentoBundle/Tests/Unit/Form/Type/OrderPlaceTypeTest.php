<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\OrderPlaceType;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;

class OrderPlaceTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderPlaceType */
    protected $type;

    protected function setUp()
    {
        $this->type = new OrderPlaceType();
    }

    protected function tearDown()
    {
        unset($this->type);
    }

    public function testInterface()
    {
        $typeName = $this->type->getName();
        $this->assertInternalType('string', $typeName);
        $this->assertNotEmpty($typeName);

        $this->assertSame(WorkflowTransitionType::class, $this->type->getParent());
    }
}
