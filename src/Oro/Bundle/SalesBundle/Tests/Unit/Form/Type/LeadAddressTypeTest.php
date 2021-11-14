<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimarySubscriber;
use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\SalesBundle\Form\Type\LeadAddressType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilder;

class LeadAddressTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeadAddressType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new LeadAddressType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf(FixAddressesPrimarySubscriber::class));
        $builder->expects($this->once())
            ->method('add')
            ->with('primary', CheckboxType::class)
            ->willReturnSelf();

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
