<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilder;

use OroCRM\Bundle\MagentoBundle\Form\Type\CustomerAddressType;

class CustomerAddressTypeTest extends \PHPUnit_Framework_TestCase
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

        $this->assertSame('oro_typed_address', $parent);
    }

    public function testBuildForm()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|FormBuilder $builder */
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
