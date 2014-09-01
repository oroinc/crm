<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\SalesBundle\Form\Type\B2bCustomerType;

use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class B2bCustomerTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $nameFormatter;

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetName()
    {
        $type = new B2bCustomerType($this->router, $this->nameFormatter);
        $this->assertEquals('orocrm_sales_B2bCustomer', $type->getName());
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(9))
            ->method('add')
            ->will(
                $this->returnValueMap(
                    [
                        [['name', 'text'], $this->returnSelf()],
                        [['account', 'orocrm_account_select'], $this->returnSelf()],
                        [['contact', 'orocrm_contact_select'], $this->returnSelf()],
                        [['tags', 'oro_tag_select'], $this->returnSelf()],
                        [['channel', 'orocrm_channel_select_type'], $this->returnSelf()],
                        [['leads', 'oro_multiple_entity'], $this->returnSelf()],
                        [['opportunities', 'oro_multiple_entity'], $this->returnSelf()],
                        [['shippingAddress', 'oro_address'], $this->returnSelf()],
                        [['billingAddress', 'oro_address'], $this->returnSelf()]
                    ]
                )
            );

        $type = new B2bCustomerType($this->router, $this->nameFormatter);
        $type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $type = new B2bCustomerType($this->router, $this->nameFormatter);
        $type->setDefaultOptions($resolver);
    }

    public function testFinishView()
    {
        $this->router->expects($this->at(0))
            ->method('generate')
            ->with('orocrm_sales_widget_leads_assign', array('id' => 100))
            ->will($this->returnValue('/test-path/100'));

        $this->router->expects($this->at(1))
            ->method('generate')
            ->with('orocrm_sales_widget_opportunities_assign', array('id' => 100))
            ->will($this->returnValue('/test-info/100'));

        $b2bCustomer = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\B2bCustomer')
            ->disableOriginalConstructor()
            ->getMock();
        $b2bCustomer->expects($this->exactly(2))
            ->method('getId')
            ->will($this->returnValue(100));
        $b2bCustomer->expects($this->once())
            ->method('getLeads')
            ->will($this->returnValue(new ArrayCollection(array())));
        $b2bCustomer->expects($this->once())
            ->method('getOpportunities')
            ->will($this->returnValue(new ArrayCollection(array())));
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($b2bCustomer));

        $formView = new FormView();
        $type = new B2bCustomerType($this->router, $this->nameFormatter);
        $type->finishView(
            $formView,
            $form,
            array()
        );
    }
}
