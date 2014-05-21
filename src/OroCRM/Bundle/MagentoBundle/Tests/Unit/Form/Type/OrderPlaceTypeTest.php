<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MagentoBundle\Form\Type\OrderPlaceType;

class OrderPlaceTypeTest extends \PHPUnit_Framework_TestCase
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

        $this->assertSame('oro_workflow_transition', $this->type->getParent());
    }
}
