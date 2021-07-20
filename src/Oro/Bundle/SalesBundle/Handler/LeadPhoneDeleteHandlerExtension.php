<?php

namespace Oro\Bundle\SalesBundle\Handler;

use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The delete handler extension for LeadPhone entity.
 */
class LeadPhoneDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
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
        /** @var LeadPhone $entity */

        $lead = $entity->getOwner();
        if (null === $lead) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('EDIT', $lead)) {
            throw $this->createAccessDeniedException();
        }

        if ($entity->isPrimary() && $lead->getPhones()->count() !== 1) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('oro.sales.validation.lead.phones.delete.more_one', [], 'validators')
            );
        }
    }
}
