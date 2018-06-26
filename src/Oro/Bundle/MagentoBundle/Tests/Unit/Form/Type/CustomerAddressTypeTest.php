<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\TypedAddressType;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerAddressType;
use Symfony\Component\Form\FormBuilder;

class CustomerAddressTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerAddressType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CustomerAddressType();
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

        $parent = $this->type->getParent();
        $this->assertInternalType('string', $parent);
        $this->assertNotEmpty($parent);

        $this->assertSame(TypedAddressType::class, $parent);
    }

    public function testBuildForm()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|FormBuilder $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->atLeastOnce())
            ->method('add')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                $this->callback(
                    function ($item) {
                        $this->assertInternalType('array', $item);
                        $this->assertArrayHasKey('label', $item);

                        return true;
                    }
                )
            )
            ->
            will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }
}
