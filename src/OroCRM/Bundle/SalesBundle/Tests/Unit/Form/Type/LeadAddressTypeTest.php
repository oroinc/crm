<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\SalesBundle\Form\Type\LeadAddressType;

class LeadAddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LeadAddressType
     */
    protected $type;

    protected function setUp()
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
                ->with('primary', 'checkbox')
                ->will($this->returnSelf());

        $this->type->buildForm($builder, ['single_form' => true]);
    }

    public function testName()
    {
        $this->assertEquals('orocrm_sales_lead_address', $this->type->getName());
    }

    public function getParent()
    {
        $this->assertEquals('oro_address', $this->type->getParent());
    }
}
