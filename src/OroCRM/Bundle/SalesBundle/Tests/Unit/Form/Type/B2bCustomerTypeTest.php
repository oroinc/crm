<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\SalesBundle\Form\Type\B2bCustomerType;

class B2bCustomerTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $type = new B2bCustomerType();
        $this->assertEquals('orocrm_sales_b2bcustomer', $type->getName());
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(8))
            ->method('add')
            ->will(
                $this->returnValueMap(
                    [
                        [['name', 'text'], $this->returnSelf()],
                        [['account', 'orocrm_account_select'], $this->returnSelf()],
                        [['contact', 'orocrm_contact_select'], $this->returnSelf()],
                        [['channel', 'orocrm_channel_select_type'], $this->returnSelf()],
                        [['shippingAddress', 'oro_address'], $this->returnSelf()],
                        [['billingAddress', 'oro_address'], $this->returnSelf()]
                    ]
                )
            );

        $type = new B2bCustomerType();
        $type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $type = new B2bCustomerType();
        $type->setDefaultOptions($resolver);
    }
}
