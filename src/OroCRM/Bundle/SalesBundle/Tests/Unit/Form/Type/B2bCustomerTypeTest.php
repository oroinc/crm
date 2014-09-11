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
        $this->assertEquals('orocrm_sales_b2bcustomer', $type->getName());
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
        $b2bCustomer = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\B2bCustomer')
            ->disableOriginalConstructor()->getMock();
        $b2bCustomer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(100));
        $b2bCustomer->expects($this->once())
            ->method('getLeads')
            ->will($this->returnValue(new ArrayCollection([])));
        $b2bCustomer->expects($this->once())
            ->method('getOpportunities')
            ->will($this->returnValue(new ArrayCollection([])));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getData')
            ->will($this->returnValue($b2bCustomer));

        $formView           = new FormView();
        $leadsView          = new FormView($formView);
        $opportunitiesView  = new FormView($formView);
        $formView->children = ['leads' => $leadsView, 'opportunities' => $opportunitiesView];

        $type = new B2bCustomerType($this->router, $this->nameFormatter);
        $type->finishView(
            $formView,
            $form,
            []
        );

        $this->assertArrayHasKey('selection_route', $leadsView->vars);
        $this->assertArrayHasKey('selection_route_parameters', $leadsView->vars);
        $this->assertArrayHasKey('initial_elements', $leadsView->vars);
        $this->assertInternalType('array', $leadsView->vars['initial_elements']);

        $this->assertArrayHasKey('selection_route', $opportunitiesView->vars);
        $this->assertArrayHasKey('selection_route_parameters', $opportunitiesView->vars);
        $this->assertArrayHasKey('initial_elements', $opportunitiesView->vars);
        $this->assertInternalType('array', $opportunitiesView->vars['initial_elements']);
    }
}
