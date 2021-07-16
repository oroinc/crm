<?php

namespace Oro\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CurrencyBundle\Provider\CurrencyProviderInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Contracts\Translation\TranslatorInterface;

class OpportunityListener
{
    /** @var OpportunityRelationsBuilder */
    protected $relationsBuilder;

    /** @var CurrencyProviderInterface */
    protected $currencyProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ImportStrategyHelper */
    protected $importStrategyHelper;

    public function __construct(
        OpportunityRelationsBuilder $relationsBuilder,
        CurrencyProviderInterface $currencyProvider,
        TranslatorInterface $translator,
        ImportStrategyHelper $importStrategyHelper
    ) {
        $this->relationsBuilder = $relationsBuilder;
        $this->currencyProvider = $currencyProvider;
        $this->translator = $translator;
        $this->importStrategyHelper = $importStrategyHelper;
    }

    public function onProcessBefore(StrategyEvent $event)
    {
        /** @var Opportunity $entity */
        $entity = $event->getEntity();
        if (!$entity instanceof Opportunity) {
            return;
        }

        if (null !== $entity->getId()) {
            return;
        }

        $this->validateCurrencies($entity, $event);
    }

    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Opportunity) {
            return;
        }

        if ($entity->getId() && !$this->validateCurrencies($entity, $event)) {
            return;
        }

        if (!$entity->getCustomerAssociation()) {
            return;
        }

        $customer = $entity->getCustomerAssociation()->getCustomerTarget();

        if ($customer) {
            if (!$customer->getAccount()) {
                // new Account for new B2bCustomer
                $account = new Account();
                $account->setName($customer->getName());
                $customer->setAccount($account);
            }
        }

        $this->relationsBuilder->buildAll($entity);
    }

    /**
     * @param Opportunity $entity
     * @param StrategyEvent $event
     *
     * @return bool
     */
    protected function validateCurrencies(Opportunity $entity, StrategyEvent $event)
    {
        $entityCurrencies = $this->getNotNullCurrencyFields($entity);

        if (0 === count($entityCurrencies)) {
            return true;
        }

        $invalidCurrencies = array_unique(
            array_diff(
                $entityCurrencies,
                $this->currencyProvider->getCurrencyList()
            )
        );

        $countOfInvalidCurrencies = count($invalidCurrencies);
        if (0 === $countOfInvalidCurrencies) {
            return true;
        }

        $event->getContext()->incrementErrorEntriesCount();
        $event->setEntity(null);
        if (1 === $countOfInvalidCurrencies) {
            $errorMessage = $this->translator->trans(
                'oro.sales.opportunity.importexport.invalid_currency',
                ['%currency%' => sprintf('"%s"', reset($invalidCurrencies))]
            );
        } else {
            $errorMessage = $this->translator->trans(
                'oro.sales.opportunity.importexport.invalid_currencies',
                ['%currencies%' => sprintf('"%s"', implode('", "', $invalidCurrencies))]
            );
        }
        $this->importStrategyHelper->addValidationErrors([$errorMessage], $event->getContext());

        return false;
    }

    /**
     * @param Opportunity $entity
     * @return array
     */
    protected function getNotNullCurrencyFields(Opportunity $entity)
    {
        $budgetAmountCurrency = $entity->getBudgetAmountCurrency();
        if (null === $entity->getBudgetAmountValue()) {
            $budgetAmountCurrency = null;
            $entity->setBudgetAmountCurrency(null);
            $entity->setBaseBudgetAmountValue(null);
        } elseif (null === $budgetAmountCurrency) {
            $entity->setBudgetAmountCurrency(
                $this->currencyProvider->getDefaultCurrency()
            );
            $entity->setBaseBudgetAmountValue($entity->getBudgetAmountValue());
        }

        $closeRevenueCurrency = $entity->getCloseRevenueCurrency();
        if (null === $entity->getCloseRevenueValue()) {
            $closeRevenueCurrency = null;
            $entity->setCloseRevenueCurrency(null);
            $entity->setBaseCloseRevenueValue(null);
        } elseif (null === $closeRevenueCurrency) {
            $entity->setCloseRevenueCurrency(
                $this->currencyProvider->getDefaultCurrency()
            );
            $entity->setBaseCloseRevenueValue($entity->getCloseRevenueValue());
        }

        return array_filter([$budgetAmountCurrency, $closeRevenueCurrency]);
    }
}
