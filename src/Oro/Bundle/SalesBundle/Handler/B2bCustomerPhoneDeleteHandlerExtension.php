<?php

namespace Oro\Bundle\SalesBundle\Handler;

use Oro\Bundle\EntityBundle\Handler\AbstractEntityDeleteHandlerExtension;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The delete handler extension for B2bCustomerPhone entity.
 */
class B2bCustomerPhoneDeleteHandlerExtension extends AbstractEntityDeleteHandlerExtension
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
        /** @var B2bCustomerPhone $entity */

        $customer = $entity->getOwner();
        if (null === $customer) {
            return;
        }

        if (!$this->authorizationChecker->isGranted('EDIT', $customer)) {
            throw $this->createAccessDeniedException();
        }

        if ($entity->isPrimary() && $customer->getPhones()->count() !== 1) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('oro.sales.validation.b2bcustomer.phones.delete.more_one', [], 'validators')
            );
        }
    }
}
