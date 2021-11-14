<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Handler\ContactEmailDeleteHandlerExtension;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactEmailDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ContactEmailDeleteHandlerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->extension = new ContactEmailDeleteHandlerExtension(
            $this->authorizationChecker,
            $this->translator
        );
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWhenNoOwner()
    {
        $contactEmail = new ContactEmail();

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenAccessGranted()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: access denied.');

        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(false);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenPrimaryEmailIsDeletedAndThereIsOtherEmails()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: translated exception message.');

        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);
        $contact->addEmail(new ContactEmail());

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.contact.validators.emails.delete.more_one', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenPrimaryEmailIsDeletedIfThereIsNoOtherEmails()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenNotPrimaryEmailIsDeleted()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactDoesNotHaveOtherIdentification()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: translated exception message.');

        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.contact.validators.contact.has_information', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactHasFirstName()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactHasLastName()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setLastName('ln');
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactHasPhone()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->addPhone(new ContactPhone());
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }
}
