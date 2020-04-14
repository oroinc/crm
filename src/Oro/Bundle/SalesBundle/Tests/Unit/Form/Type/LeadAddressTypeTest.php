<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\SalesBundle\Form\Type\LeadAddressType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class LeadAddressTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LeadAddressType
     */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new LeadAddressType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
                ->method('addEventSubscriber')
                ->with(
                    $this->isInstanceOf('Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimarySubscriber')
                );
        $builder->expects($this->once())
                ->method('add')
                ->with('primary', CheckboxType::class)
                ->will($this->returnSelf());

        $this->type->buildForm($builder, ['single_form' => true]);
    }

    public function testName()
    {
        $this->assertEquals('oro_sales_lead_address', $this->type->getName());
    }

    public function getParent()
    {
        $this->assertEquals(AddressType::class, $this->type->getParent());
    }
}
