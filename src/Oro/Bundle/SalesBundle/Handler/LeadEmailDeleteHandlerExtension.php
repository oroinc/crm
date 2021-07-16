<?php

namespace Oro\Bundle\SalesBundle\Handler;

use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The delete handler extension for LeadEmail entity.
 */
class LeadEmailDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
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
        /** @var LeadEmail $entity */

        $lead = $entity->getOwner();
        if (null === $lead) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('EDIT', $lead)) {
            throw $this->createAccessDeniedException();
        }

        if ($entity->isPrimary() && $lead->getEmails()->count() !== 1) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('oro.sales.validation.lead.emails.delete.more_one', [], 'validators')
            );
        }
    }
}
