<?php

namespace Oro\Bundle\ContactBundle\Handler;

use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The delete handler extension for ContactEmail entity.
 */
class ContactEmailDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function assertDeleteGranted($entity): void
    {
        /** @var ContactEmail $entity */

        $contact = $entity->getOwner();
        if (null === $contact) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('EDIT', $contact)) {
            throw $this->createAccessDeniedException();
        }

        if ($entity->isPrimary() && $contact->getEmails()->count() !== 1) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('oro.contact.validators.emails.delete.more_one', [], 'validators')
            );
        }

        if (!$contact->getFirstName()
            && !$contact->getLastName()
            && $contact->getEmails()->count() <= 1
            && $contact->getPhones()->count() === 0
        ) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('oro.contact.validators.contact.has_information', [], 'validators')
            );
        }
    }
}
