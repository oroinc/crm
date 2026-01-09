<?php

namespace Oro\Bundle\SalesBundle\Entity\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Recalculates and updates various fields of an updated opportunity.
 */
class OpportunityListener
{
    private const VALUABLE_CHANGE_SET_KEYS = [
        'status' => 0,
        'budgetAmountValue' => 1,
        'budgetAmountCurrency' => 2,
        'closeRevenueValue' => 3,
        'closeRevenueCurrency' => 4
    ];

    private RateConverterInterface $rateConverter;
    private DefaultCurrencyProviderInterface $defaultCurrencyProvider;

    public function __construct(
        RateConverterInterface $rateConverter,
        DefaultCurrencyProviderInterface $defaultCurrencyProvider
    ) {
        $this->rateConverter = $rateConverter;
        $this->defaultCurrencyProvider = $defaultCurrencyProvider;
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getObjectManager();
        $uow = $em->getUnitOfWork();
        $this->processEntities($uow->getScheduledEntityInsertions(), $em, $uow);
        $this->processEntities($uow->getScheduledEntityUpdates(), $em, $uow);
    }

    private function processEntities(array $entities, EntityManagerInterface $em, UnitOfWork $uow): void
    {
        foreach ($entities as $opportunity) {
            if (!$opportunity instanceof Opportunity) {
                continue;
            }

            $entityChangeSet = $uow->getEntityChangeSet($opportunity);
            $changedKeys = array_intersect_key(self::VALUABLE_CHANGE_SET_KEYS, $entityChangeSet);
            if (!$changedKeys) {
                continue;
            }

            $newStatusId = $opportunity->getStatus() ? $opportunity->getStatus()->getInternalId() : null;
            $isOpportunityChanged = $this->updateBaseBudgetAmountFields($newStatusId, $opportunity);
            $isOpportunityChanged |= $this->onStatusChange($newStatusId, $entityChangeSet, $opportunity);

            if ($isOpportunityChanged) {
                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata(Opportunity::class),
                    $opportunity
                );
            }
        }
    }

    private function onStatusChange(?string $newStatusId, array $entityChangeSet, Opportunity $opportunity): bool
    {
        $isOpportunityChanged = false;

        if (
            !\in_array($newStatusId, Opportunity::getClosedStatuses(), true)
            && $this->isNotNullBaseAmountFieldsExist($opportunity)
        ) {
            $opportunity->getBudgetAmount()->setBaseCurrencyValue(null);
            $opportunity->getCloseRevenue()->setBaseCurrencyValue(null);
            $isOpportunityChanged = true;
        }

        if (
            !isset($entityChangeSet['serialized_data']) ||
            empty(array_column($entityChangeSet['serialized_data'], 'status'))
        ) {
            return $isOpportunityChanged;
        }

        /** @var EnumOptionInterface|null $oldStatus */
        $oldStatus = isset($entityChangeSet['serialized_data'][0]['status']) ?
            $entityChangeSet['serialized_data'][0]['status'] : null;
        $oldStatusId = $oldStatus ? ExtendHelper::getEnumInternalId($oldStatus) : null;
        $valuableChanges = array_intersect([$oldStatusId, $newStatusId], Opportunity::getClosedStatuses());

        if (\in_array($newStatusId, $valuableChanges, true)) {
            $opportunity->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $isOpportunityChanged = true;
        }

        if (\in_array($oldStatusId, $valuableChanges, true)) {
            $opportunity->setClosedAt(null);
            $isOpportunityChanged = true;
        }

        return $isOpportunityChanged;
    }

    private function isNotNullBaseAmountFieldsExist(Opportunity $opportunity): bool
    {
        return
            null !== $opportunity->getBaseBudgetAmountValue()
            || null !== $opportunity->getBaseCloseRevenueValue();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function updateBaseBudgetAmountFields(?string $newStatusId, Opportunity $opportunity): bool
    {
        $isOpportunityChanged = false;
        if (!\in_array($newStatusId, Opportunity::getClosedStatuses(), true)) {
            return $isOpportunityChanged;
        }

        $defaultCurrency = $this->defaultCurrencyProvider->getDefaultCurrency();
        $budgetAmount = $opportunity->getBudgetAmount();
        if ($budgetAmount && null !== $budgetAmount->getValue()) {
            if (
                $budgetAmount->getCurrency() === $defaultCurrency
                && null !== $budgetAmount->getBaseCurrencyValue()
            ) {
                $opportunity->setBaseBudgetAmountValue($budgetAmount->getValue());
                $isOpportunityChanged = true;
            } elseif (null === $budgetAmount->getBaseCurrencyValue()) {
                $baseBudgetAmount = $this->rateConverter->getBaseCurrencyAmount($budgetAmount);
                $opportunity->setBaseBudgetAmountValue($baseBudgetAmount);
                $isOpportunityChanged = true;
            }
        }

        $closeRevenue = $opportunity->getCloseRevenue();
        if ($closeRevenue && null !== $closeRevenue->getValue()) {
            if (
                $closeRevenue->getCurrency() === $defaultCurrency
                && null !== $closeRevenue->getBaseCurrencyValue()
            ) {
                $opportunity->setBaseCloseRevenueValue($closeRevenue->getValue());
                $isOpportunityChanged = true;
            } elseif (null === $closeRevenue->getBaseCurrencyValue()) {
                $closeRevenueAmount = $this->rateConverter->getBaseCurrencyAmount($closeRevenue);
                $opportunity->setBaseCloseRevenueValue($closeRevenueAmount);
                $isOpportunityChanged = true;
            }
        }

        return $isOpportunityChanged;
    }
}
