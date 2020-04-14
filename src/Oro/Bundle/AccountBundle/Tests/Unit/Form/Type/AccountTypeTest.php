<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AccountBundle\Form\Type\AccountType;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccountTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $router;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $entityNameResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    protected function setUp(): void
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityNameResolver = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityNameResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->will($this->returnValue(true));

        $builder->expects($this->at(0))
            ->method('add')
            ->with('name', TextType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with('default_contact', EntityIdentifierType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('contacts', MultipleEntityType::class)
            ->will($this->returnSelf());

        $type = new AccountType($this->router, $this->entityNameResolver, $this->authorizationChecker);
        $type->buildForm($builder, []);
    }

    public function testAddEntityFieldsWithoutContactPermission()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->will($this->returnValue(false));

        $builder->expects($this->at(0))
            ->method('add')
            ->with('name', TextType::class)
            ->will($this->returnSelf());

        $type = new AccountType($this->router, $this->entityNameResolver, $this->authorizationChecker);
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        /** @var OptionsResolver $resolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $type = new AccountType($this->router, $this->entityNameResolver, $this->authorizationChecker);
        $type->configureOptions($resolver);
    }

    public function testFinishView()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->will($this->returnValue(true));

        $this->router->expects($this->at(0))
            ->method('generate')
            ->with('oro_account_widget_contacts_info', array('id' => 100))
            ->will($this->returnValue('/test-path/100'));
        $this->router->expects($this->at(1))
            ->method('generate')
            ->with('oro_contact_info', array('id' => 1))
            ->will($this->returnValue('/test-info/1'));

        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();
        $contact->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $phone = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\ContactPhone')
            ->disableOriginalConstructor()
            ->getMock();
        $phone->expects($this->once())
            ->method('getPhone')
            ->will($this->returnValue('911'));
        $contact->expects($this->once())
            ->method('getPrimaryPhone')
            ->will($this->returnValue($phone));
        $email = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\ContactEmail')
            ->disableOriginalConstructor()
            ->getMock();
        $email->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('john.doe@dummy.net'));
        $contact->expects($this->once())
            ->method('getPrimaryEmail')
            ->will($this->returnValue($email));
        $contacts = new ArrayCollection(array($contact));

        $this->entityNameResolver->expects($this->once())
            ->method('getName')
            ->with($contact)
            ->will($this->returnValue('John Doe'));

        $account = $this->getMockBuilder('Oro\Bundle\AccountBundle\Entity\Account')
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

        $type = new AccountType($this->router, $this->entityNameResolver, $this->authorizationChecker);
        $type->finishView($formView, $form, array());

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

    public function testFinishViewWithoutContactPermission()
    {
        $this->authorizationChecker->expects($this->exactly(1))
            ->method('isGranted')
            ->with('oro_contact_view')
            ->will($this->returnValue(false));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $formView = new FormView();
        $type = new AccountType($this->router, $this->entityNameResolver, $this->authorizationChecker);
        $type->finishView($formView, $form, array());

        $this->assertTrue(empty($formView->children['contacts']));
    }
}
