<?php
declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Listens on oro_importexport.strategy.process_after to validate customer association for leads and opportunities.
 */
class CustomerAssociationListener
{
    protected TranslatorInterface $translator;
    protected ImportStrategyHelper $importStrategyHelper;
    protected AccountCustomerManager $accountCustomerManager;
    protected DoctrineHelper $doctrineHelper;

    public function __construct(
        TranslatorInterface $translator,
        DoctrineHelper $doctrineHelper,
        ImportStrategyHelper $importStrategyHelper,
        AccountCustomerManager $accountCustomerManager
    ) {
        $this->translator = $translator;
        $this->doctrineHelper = $doctrineHelper;
        $this->importStrategyHelper = $importStrategyHelper;
        $this->accountCustomerManager = $accountCustomerManager;
    }

    public function onProcessAfter(StrategyEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Opportunity || $entity instanceof Lead) {
            $this->validateCustomerAssociation($entity, $event);
        }
    }

    protected function validateCustomerAssociation(object $entity, StrategyEvent $event): void
    {
        /** @var Customer $customerAssociation */
        $customerAssociation = $entity->getCustomerAssociation();
        if (null === $customerAssociation && $entity instanceof Lead) {
            return;
        }

        // reject entity if customer association account is empty
        if (!$customerAssociation || !$customerAssociation->getAccount()) {
            $this->addValidationError($event, 'oro.sales.customer.importexport.empty_account');

            return;
        }

        $account = $customerAssociation->getAccount();
        $target = $customerAssociation->getTarget();
        if ($target->getId()) {
            // use existing customer association for customer target if accounts are the same, reject otherwise
            $oldCustomerAssociation = $this->accountCustomerManager->getAccountCustomerByTarget($target, false);
            if ($oldCustomerAssociation) {
                if ($oldCustomerAssociation->getAccount() === $account) {
                    $entity->setCustomerAssociation($oldCustomerAssociation);
                } else {
                    $this->addValidationError($event, 'oro.sales.customer.importexport.not_matched_account');
                }
            }
        }
    }

    protected function addValidationError(StrategyEvent $event, string $error): void
    {
        $this->importStrategyHelper->addValidationErrors(
            [$this->translator->trans($error)],
            $event->getContext()
        );

        $event->getContext()->incrementErrorEntriesCount();
        $event->setEntity(null);
    }
}
