<?php

namespace Oro\Bundle\SalesBundle\Entity\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface;
use Oro\Bundle\CurrencyBundle\Provider\DefaultCurrencyProviderInterface;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Recalculates and updates various fields of an updated opportunity.
 */
class OpportunityListener
{
    protected $valuableChangesetKeys = [
        'status',
        'budgetAmountValue',
        'budgetAmountCurrency',
        'closeRevenueValue',
        'closeRevenueCurrency'
    ];

    /**
     * @var RateConverterInterface
     */
    protected $rateConverter;

    /** @var DefaultCurrencyProviderInterface */
    protected $currencyProvider;

    public function __construct(
        RateConverterInterface $rateConverter,
        DefaultCurrencyProviderInterface $currencyProvider
    ) {
        $this->rateConverter = $rateConverter;
        $this->currencyProvider = $currencyProvider;
    }

    public function onFlush(OnFlushEventArgs $event)
    {
        $em         = $event->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();
        $entities   = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates()
        );
        foreach ($entities as $opportunity) {
            if ($opportunity instanceof Opportunity) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($opportunity);
                $changedKeys = array_intersect_key(
                    array_flip($this->valuableChangesetKeys),
                    $entityChangeSet
                );

                if (0 === count($changedKeys)) {
                    return;
                }

                /** @var AbstractEnumValue $oldStatus */
                $newStatusId = $opportunity->getStatus() ? $opportunity->getStatus()->getId() : null;
                $isOppportunityChanged = $this->updateBaseBudgetAmountFields(
                    $newStatusId,
                    $opportunity,
                    $entityChangeSet
                );
                $isOppportunityChanged |= $this->onStatusChange($newStatusId, $entityChangeSet, $opportunity);

                if ($isOppportunityChanged) {
                    $unitOfWork->recomputeSingleEntityChangeSet(
                        $em->getClassMetadata(Opportunity::class),
                        $opportunity
                    );
                }
            }
        }
    }

    /**
     * @param string     $newStatusId
     * @param array       $entityChangeSet
     * @param Opportunity $opportunity
     *
     * @return bool
     */
    protected function onStatusChange($newStatusId, array $entityChangeSet, Opportunity $opportunity)
    {
        $isOppportunityChanged = false;

        if (! in_array($newStatusId, Opportunity::getClosedStatuses(), true)
            && $this->isNotNullBaseAmountFieldsExist($opportunity)) {
            $opportunity->getBudgetAmount()->setBaseCurrencyValue(null);
            $opportunity->getCloseRevenue()->setBaseCurrencyValue(null);
            $isOppportunityChanged = true;
        }

        if (empty($entityChangeSet['status'])) {
            return $isOppportunityChanged;
        }

        $oldStatus       = $entityChangeSet['status'][0];
        $oldStatusId     = $oldStatus ? $oldStatus->getId() : null;
        $valuableChanges = array_intersect([$oldStatusId, $newStatusId], Opportunity::getClosedStatuses());

        if (in_array($newStatusId, $valuableChanges, true)) {
            $opportunity->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $isOppportunityChanged = true;
        }

        if (in_array($oldStatusId, $valuableChanges, true)) {
            $opportunity->setClosedAt(null);
            $isOppportunityChanged = true;
        }

        return $isOppportunityChanged;
    }

    /**
     * @param Opportunity $opportunity
     *
     * @return bool
     */
    protected function isNotNullBaseAmountFieldsExist(Opportunity $opportunity)
    {
        return null !== $opportunity->getBaseBudgetAmountValue() || null !== $opportunity->getBaseCloseRevenueValue();
    }

    /**
     * @param string      $newStatusId
     * @param Opportunity $opportunity
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function updateBaseBudgetAmountFields($newStatusId, Opportunity $opportunity, $changeSet)
    {
        $isOpportunityChanged = false;
        if (! in_array($newStatusId, Opportunity::getClosedStatuses())) {
            return $isOpportunityChanged;
        }
        $defaultCurrency = $this->currencyProvider->getDefaultCurrency();

        $budgetAmount = $opportunity->getBudgetAmount();
        if ($budgetAmount && null !== $budgetAmount->getValue()) {
            if ($defaultCurrency == $budgetAmount->getCurrency()
                && null !== $budgetAmount->getBaseCurrencyValue()) {
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
            if ($defaultCurrency == $closeRevenue->getCurrency()
                && null !== $closeRevenue->getBaseCurrencyValue()) {
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
