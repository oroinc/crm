<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Handler\LeadPhoneDeleteHandlerExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class LeadPhoneDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var LeadPhoneDeleteHandlerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->extension = new LeadPhoneDeleteHandlerExtension(
            $this->authorizationChecker,
            $this->translator
        );
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWhenNoOwner()
    {
        $leadPhone = new LeadPhone();

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($leadPhone);
    }

    public function testAssertDeleteGrantedWhenAccessGranted()
    {
        $leadPhone = new LeadPhone();
        $lead = new Lead();
        $leadPhone->setOwner($lead);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($lead))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($leadPhone);
    }

    public function testAssertDeleteGrantedWhenAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: access denied.');

        $leadPhone = new LeadPhone();
        $lead = new Lead();
        $leadPhone->setOwner($lead);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($lead))
            ->willReturn(false);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($leadPhone);
    }

    public function testAssertDeleteGrantedWhenPrimaryPhoneIsDeletedAndThereIsOtherPhones()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: translated exception message.');

        $leadPhone = new LeadPhone();
        $lead = new Lead();
        $leadPhone->setOwner($lead);

        $leadPhone->setPrimary(true);
        $lead->addPhone($leadPhone);
        $lead->addPhone(new LeadPhone());

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($lead))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.sales.validation.lead.phones.delete.more_one', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($leadPhone);
    }

    public function testAssertDeleteGrantedWhenPrimaryPhoneIsDeletedIfThereIsNoOtherPhones()
    {
        $leadPhone = new LeadPhone();
        $lead = new Lead();
        $leadPhone->setOwner($lead);

        $leadPhone->setPrimary(true);
        $lead->addPhone($leadPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($lead))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($leadPhone);
    }

    public function testAssertDeleteGrantedWhenNotPrimaryPhoneIsDeleted()
    {
        $leadPhone = new LeadPhone();
        $lead = new Lead();
        $leadPhone->setOwner($lead);

        $lead->addPhone($leadPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($lead))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($leadPhone);
    }
}
