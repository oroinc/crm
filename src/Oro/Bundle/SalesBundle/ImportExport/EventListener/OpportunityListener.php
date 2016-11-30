<?php

namespace Oro\Bundle\SalesBundle\ImportExport\EventListener;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\CurrencyBundle\Config\CurrencyConfigManager;

class OpportunityListener
{
    /** @var OpportunityRelationsBuilder */
    protected $relationsBuilder;

    /**
     * @var CurrencyConfigManager
     */
    protected $currencyConfigManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param OpportunityRelationsBuilder $relationsBuilder
     * @param CurrencyConfigManager       $currencyConfigManager
     */
    public function __construct(
        OpportunityRelationsBuilder $relationsBuilder,
        CurrencyConfigManager $currencyConfigManager,
        TranslatorInterface $translator
    ) {
        $this->relationsBuilder = $relationsBuilder;
        $this->currencyConfigManager = $currencyConfigManager;
        $this->translator = $translator;
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
                $this->currencyConfigManager->getDefaultCurrency()
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
                $this->currencyConfigManager->getDefaultCurrency()
            );
            $entity->setBaseCloseRevenueValue($entity->getCloseRevenueValue());
        }

        return array_filter([$budgetAmountCurrency, $closeRevenueCurrency]);
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

        $invalidCurrencies = array_unique(array_diff(
            $entityCurrencies,
            $this->currencyConfigManager->getCurrencyList()
        ));

        $countOfInvalidCurrencies = count($invalidCurrencies);
        if (0 === $countOfInvalidCurrencies) {
            return true;
        }

        $event->getContext()->incrementErrorEntriesCount();
        $event->setEntity(null);
        if (1 === $countOfInvalidCurrencies) {
            $errorMessage = $this->translator->trans(
                'oro.sales.opportunity.importexport.invalid_currency',
                [
                    '%currency%' => sprintf('"%s"', reset($invalidCurrencies)),
                    '%row%' => $event->getContext()->getReadOffset(),
                ]
            );
        } else {
            $errorMessage = $this->translator->trans(
                'oro.sales.opportunity.importexport.invalid_currencies',
                [
                    '%currencies%' => sprintf('"%s"', implode('", "', $invalidCurrencies)),
                    '%row%' => $event->getContext()->getReadOffset(),
                ]
            );
        }
        $event->getContext()->addError($errorMessage);
        return false;
    }

    public function onProcessBefore(StrategyEvent $event)
    {
        /**
         * @var Opportunity $entity
         */
        $entity = $event->getEntity();
        if (!$entity instanceof Opportunity) {
            return;
        }

        if (null !== $entity->getId()) {
            return;
        }

        $this->validateCurrencies($entity, $event);
    }

    /**
     * @param StrategyEvent $event
     */
    public function onProcessAfter(StrategyEvent $event)
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Opportunity) {
            return;
        }

        if ($entity->getId() && !$this->validateCurrencies($entity, $event)) {
            return;
        }

        $customer = $entity->getCustomer();
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
}
