<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use Oro\Bundle\SalesBundle\Handler\B2bCustomerPhoneDeleteHandlerExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class B2bCustomerPhoneDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var B2bCustomerPhoneDeleteHandlerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->extension = new B2bCustomerPhoneDeleteHandlerExtension(
            $this->authorizationChecker,
            $this->translator
        );
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWhenNoOwner()
    {
        $customerPhone = new B2bCustomerPhone();

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($customerPhone);
    }

    public function testAssertDeleteGrantedWhenAccessGranted()
    {
        $customerPhone = new B2bCustomerPhone();
        $customer = new B2bCustomer();
        $customerPhone->setOwner($customer);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($customer))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($customerPhone);
    }

    public function testAssertDeleteGrantedWhenAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: access denied.');

        $customerPhone = new B2bCustomerPhone();
        $customer = new B2bCustomer();
        $customerPhone->setOwner($customer);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($customer))
            ->willReturn(false);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($customerPhone);
    }

    public function testAssertDeleteGrantedWhenPrimaryPhoneIsDeletedAndThereIsOtherPhones()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: translated exception message.');

        $customerPhone = new B2bCustomerPhone();
        $customer = new B2bCustomer();
        $customerPhone->setOwner($customer);

        $customerPhone->setPrimary(true);
        $customer->addPhone($customerPhone);
        $customer->addPhone(new B2bCustomerPhone());

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($customer))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.sales.validation.b2bcustomer.phones.delete.more_one', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($customerPhone);
    }

    public function testAssertDeleteGrantedWhenPrimaryPhoneIsDeletedIfThereIsNoOtherPhones()
    {
        $customerPhone = new B2bCustomerPhone();
        $customer = new B2bCustomer();
        $customerPhone->setOwner($customer);

        $customerPhone->setPrimary(true);
        $customer->addPhone($customerPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($customer))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($customerPhone);
    }

    public function testAssertDeleteGrantedWhenNotPrimaryPhoneIsDeleted()
    {
        $customerPhone = new B2bCustomerPhone();
        $customer = new B2bCustomer();
        $customerPhone->setOwner($customer);

        $customer->addPhone($customerPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($customer))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($customerPhone);
    }
}
