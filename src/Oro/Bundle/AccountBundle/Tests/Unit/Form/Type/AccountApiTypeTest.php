<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AccountBundle\Form\Type\AccountApiType;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccountApiTypeTest extends TestCase
{
    private AccountApiType $type;

    public function init(bool $havePrivilege = true)
    {
        $entityNameResolver = $this->createMock(EntityNameResolver::class);
        $router = $this->createMock(RouterInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->any())
            ->method('isGranted')
            ->with('oro_contact_view')
            ->willReturn($havePrivilege);

        $this->type = new AccountApiType($router, $entityNameResolver, $authorizationChecker);
    }

    public function testConfigureOptions(): void
    {
        $this->init();

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }

    public function testName(): void
    {
        $this->init();
        $this->assertEquals('account', $this->type->getName());
    }

    public function testAddEntityFields(): void
    {
        $this->init();

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(3))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class],
                ['default_contact', EntityIdentifierType::class],
                ['contacts', MultipleEntityType::class]
            )
            ->willReturnSelf();
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf(EventSubscriberInterface::class));

        $this->type->buildForm($builder, []);
    }

    public function testAddEntityFieldsWithoutContactPermission(): void
    {
        $this->init(false);

        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->once())
            ->method('add')
            ->with('name', TextType::class)
            ->willReturnSelf();
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf(EventSubscriberInterface::class));

        $this->type->buildForm($builder, []);
    }
}
