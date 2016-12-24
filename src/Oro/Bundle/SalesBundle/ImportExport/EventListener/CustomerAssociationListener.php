<?php

namespace Oro\Bundle\SalesBundle\ImportExport\EventListener;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Lead;

use Oro\Bundle\SalesBundle\Entity\Opportunity;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class CustomerAssociationListener
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var ImportStrategyHelper */
    protected $importStrategyHelper;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param TranslatorInterface    $translator
     * @param DoctrineHelper         $doctrineHelper
     * @param ImportStrategyHelper   $importStrategyHelper
     * @param AccountCustomerManager $accountCustomerManager
     */
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

    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {

        $entity = $event->getEntity();
        if ($entity instanceof Opportunity || $entity instanceof Lead) {
            $this->validateCustomerAssociation($entity, $event);
        }
    }

    /**
     * @param object        $entity
     * @param StrategyEvent $event
     */
    protected function validateCustomerAssociation($entity, StrategyEvent $event)
    {
        /** @var Customer $customerAssociation */
        $customerAssociation = $entity->getCustomerAssociation();
        if ($customerAssociation === null && $entity instanceof Lead) {
            return;
        }

        // reject entity if customer association account is empty
        if (!$customerAssociation || !$customerAssociation->getAccount()) {
            $this->addValidationError($event, 'oro.sales.customer.importexport.empty_account');

            return;
        }

        $account = $customerAssociation->getAccount();
        // reject if entity has association with several customers
        if (count($customerAssociation->getCustomerTargetEntities()) > 1) {
            $this->addValidationError($event, 'oro.sales.customer.importexport.multiple_association');

            return;
        }

        $target = $customerAssociation->getTarget();
        if ($target->getId()) {
            // use existing customer association for customer target if accounts are the same, reject otherwise
            $oldCustomerAssociation = $this->accountCustomerManager->getAccountCustomerByTarget($target, false);
            if ($oldCustomerAssociation) {
                if ($oldCustomerAssociation->getAccount() == $account) {
                    $entity->setCustomerAssociation($oldCustomerAssociation);
                } else {
                    $this->addValidationError($event, 'oro.sales.customer.importexport.not_matched_account');
                }
            }
        }
    }

    /**
     * @param StrategyEvent $event
     * @param string        $error
     */
    protected function addValidationError(StrategyEvent $event, $error)
    {
        $entity = $event->getEntity();

        $this->importStrategyHelper->addValidationErrors(
            [$this->translator->trans($error)],
            $event->getContext()
        );

        $this->importStrategyHelper->getEntityManager(get_class($entity))->detach($entity);
        $event->getContext()->incrementErrorEntriesCount();
        $event->setEntity(null);
    }
}
