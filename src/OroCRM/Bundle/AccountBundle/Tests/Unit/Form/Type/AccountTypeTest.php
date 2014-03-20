<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\AccountBundle\Form\Type\AccountType;

class AccountTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountType
     */
    protected $type;

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

        $this->type = new AccountType($this->router, $this->nameFormatter);
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('add')
            ->with('name', 'text')
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with('tags', 'oro_tag_select')
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('default_contact', 'oro_entity_identifier')
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with('contacts', 'oro_multiple_entity')
            ->will($this->returnSelf());
        $builder->expects($this->at(4))
            ->method('add')
            ->with('shippingAddress', 'oro_address')
            ->will($this->returnSelf());
        $builder->expects($this->at(5))
            ->method('add')
            ->with('billingAddress', 'oro_address')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_account', $this->type->getName());
    }

    public function testFinishView()
    {
        $this->router->expects($this->at(0))
            ->method('generate')
            ->with('orocrm_account_widget_contacts_info', array('id' => 100))
            ->will($this->returnValue('/test-path/100'));
        $this->router->expects($this->at(1))
            ->method('generate')
            ->with('orocrm_contact_info', array('id' => 1))
            ->will($this->returnValue('/test-info/1'));

        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();
        $contact->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $phone = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\ContactPhone')
            ->disableOriginalConstructor()
            ->getMock();
        $phone->expects($this->once())
            ->method('getPhone')
            ->will($this->returnValue('911'));
        $contact->expects($this->once())
            ->method('getPrimaryPhone')
            ->will($this->returnValue($phone));
        $email = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\ContactEmail')
            ->disableOriginalConstructor()
            ->getMock();
        $email->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('john.doe@dummy.net'));
        $contact->expects($this->once())
            ->method('getPrimaryEmail')
            ->will($this->returnValue($email));
        $contacts = new ArrayCollection(array($contact));

        $this->nameFormatter->expects($this->once())
            ->method('format')
            ->with($contact)
            ->will($this->returnValue('John Doe'));

        $account = $this->getMockBuilder('OroCRM\Bundle\AccountBundle\Entity\Account')
            ->disableOriginalConstructor()
            ->getMock();
        $account->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(100));
        $account->expects($this->once())
            ->method('getContacts')
            ->will($this->returnValue($contacts));
        $account->expects($this->exactly(2))
            ->method('getDefaultContact')
            ->will($this->returnValue($contact));
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($account));

        $formView = new FormView();
        $contactsFormView = new FormView($formView);
        $formView->children['contacts'] = $contactsFormView;
        $this->type->finishView($formView, $form, array());

        $this->assertEquals($contactsFormView->vars['grid_url'], '/test-path/100');
        $expectedInitialElements = array(
            array(
                'id' => 1,
                'label' => 'John Doe',
                'link' => '/test-info/1',
                'isDefault' => true,
                'extraData' => array(
                    array('label' => 'Phone', 'value' => '911'),
                    array('label' => 'Email', 'value' => 'john.doe@dummy.net')
                )
            )
        );
        $this->assertEquals($expectedInitialElements, $contactsFormView->vars['initial_elements']);
    }
}
