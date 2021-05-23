<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Form\Type\AccountType;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccountTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var EntityNameResolver|\PHPUnit\Framework\MockObject\MockObject */
    private $entityNameResolver;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var AccountType */
    private $type;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->entityNameResolver = $this->createMock(EntityNameResolver::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->type = new AccountType($this->router, $this->entityNameResolver, $this->authorizationChecker);
    }

    public function testAddEntityFields()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->willReturn(true);

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class],
                ['default_contact', EntityIdentifierType::class],
                ['contacts', MultipleEntityType::class]
            )
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }

    public function testAddEntityFieldsWithoutContactPermission()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->willReturn(false);

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->once())
            ->method('add')
            ->with('name', TextType::class)
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }

    public function testFinishView()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->willReturn(true);

        $this->router->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                ['oro_account_widget_contacts_info', ['id' => 100], RouterInterface::ABSOLUTE_PATH, '/test-path/100'],
                ['oro_contact_info', ['id' => 1], RouterInterface::ABSOLUTE_PATH, '/test-info/1']
            ]);

        $contact = new Contact();
        $contact->setId(1);
        $phone = new ContactPhone();
        $phone->setPhone('911');
        $contact->addPhone($phone);
        $contact->setPrimaryPhone($phone);
        $email = new ContactEmail();
        $email->setEmail('john.doe@dummy.net');
        $contact->addEmail($email);
        $contact->setPrimaryEmail($email);

        $account = new Account();
        $account->setId(100);
        $account->addContact($contact);
        $account->setDefaultContact($contact);

        $this->entityNameResolver->expects($this->once())
            ->method('getName')
            ->with($contact)
            ->willReturn('John Doe');

        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($account);

        $formView = new FormView();
        $contactsFormView = new FormView($formView);
        $formView->children['contacts'] = $contactsFormView;

        $this->type->finishView($formView, $form, []);

        $this->assertEquals('/test-path/100', $contactsFormView->vars['grid_url']);
        $this->assertEquals(
            [
                [
                    'id' => 1,
                    'label' => 'John Doe',
                    'link' => '/test-info/1',
                    'isDefault' => true,
                    'extraData' => [
                        ['label' => 'Phone', 'value' => '911'],
                        ['label' => 'Email', 'value' => 'john.doe@dummy.net']
                    ]
                ]
            ],
            $contactsFormView->vars['initial_elements']
        );
    }

    public function testFinishViewWithoutContactPermission()
    {
        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->willReturn(false);

        $formView = new FormView();
        $this->type->finishView($formView, $this->createMock(Form::class), []);

        $this->assertArrayNotHasKey('contacts', $formView->children);
    }
}
