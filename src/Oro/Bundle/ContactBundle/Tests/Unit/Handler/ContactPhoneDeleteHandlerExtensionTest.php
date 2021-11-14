<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Handler\ContactPhoneDeleteHandlerExtension;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactPhoneDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ContactPhoneDeleteHandlerExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->extension = new ContactPhoneDeleteHandlerExtension(
            $this->authorizationChecker,
            $this->translator
        );
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWhenNoOwner()
    {
        $contactPhone = new ContactPhone();

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenAccessGranted()
    {
        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contact->setFirstName('fn');

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: access denied.');

        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(false);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenPrimaryPhoneIsDeletedAndThereIsOtherPhones()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: translated exception message.');

        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contactPhone->setPrimary(true);
        $contact->addPhone($contactPhone);
        $contact->addPhone(new ContactPhone());

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.contact.validators.phones.delete.more_one', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenPrimaryPhoneIsDeletedIfThereIsNoOtherPhones()
    {
        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contact->setFirstName('fn');
        $contactPhone->setPrimary(true);
        $contact->addPhone($contactPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenNotPrimaryPhoneIsDeleted()
    {
        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contact->setFirstName('fn');
        $contact->addPhone($contactPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenLastPhoneIsDeletedAndContactDoesNotHaveOtherIdentification()
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('The delete operation is forbidden. Reason: translated exception message.');

        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contactPhone->setPrimary(true);
        $contact->addPhone($contactPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.contact.validators.contact.has_information', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenLastPhoneIsDeletedAndContactHasFirstName()
    {
        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contact->setFirstName('fn');
        $contactPhone->setPrimary(true);
        $contact->addPhone($contactPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenLastPhoneIsDeletedAndContactHasLastName()
    {
        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contact->setLastName('ln');
        $contactPhone->setPrimary(true);
        $contact->addPhone($contactPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }

    public function testAssertDeleteGrantedWhenLastPhoneIsDeletedAndContactHasEmail()
    {
        $contactPhone = new ContactPhone();
        $contact = new Contact();
        $contactPhone->setOwner($contact);

        $contact->addEmail(new ContactEmail());
        $contactPhone->setPrimary(true);
        $contact->addPhone($contactPhone);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactPhone);
    }
}
