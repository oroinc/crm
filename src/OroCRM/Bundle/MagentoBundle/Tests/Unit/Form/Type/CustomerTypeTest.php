<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\MagentoBundle\Form\Type\CustomerType;

class CustomerTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerType */
    protected $type;

    protected function setUp()
    {
        $this->type = new CustomerType('\stdClass', '\stdClass');
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

        $builder->expects($this->once())
            ->method('addEventListener')
            ->withConsecutive([FormEvents::SUBMIT]);

        $this->type->buildForm($builder, []);
    }
}
