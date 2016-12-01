<?php

namespace Oro\Bundle\SalesBundle\Entity\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface;

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

    /**
     * @param RateConverterInterface $rateConverter
     */
    public function __construct(RateConverterInterface $rateConverter)
    {
        $this->rateConverter = $rateConverter;
    }

    /**
     * @param OnFlushEventArgs $event
     */
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
                $isOppportunityChanged = $this->updateBaseBudgetAmountFields($newStatusId, $opportunity) ||
                    $this->onStatusChange($newStatusId, $entityChangeSet, $opportunity);

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
     */
    protected function updateBaseBudgetAmountFields($newStatusId, Opportunity $opportunity)
    {
        $isOppportunityChanged = false;
        if (! in_array($newStatusId, Opportunity::getClosedStatuses())) {
            return $isOppportunityChanged;
        }

        if ($opportunity->getBudgetAmount() instanceof MultiCurrency
            && null === $opportunity->getBaseBudgetAmountValue()
            && null !== $opportunity->getBudgetAmountValue()
        ) {
            $baseBudgetAmount = $this->rateConverter->getBaseCurrencyAmount($opportunity->getBudgetAmount());
            $opportunity->setBaseBudgetAmountValue($baseBudgetAmount);
            $isOppportunityChanged = true;
        }

        if ($opportunity->getCloseRevenue() instanceof MultiCurrency
            && null === $opportunity->getBaseCloseRevenueValue()
            && null !== $opportunity->getCloseRevenueValue()
        ) {
            $closeRevenueAmount = $this->rateConverter->getBaseCurrencyAmount($opportunity->getCloseRevenue());
            $opportunity->setBaseCloseRevenueValue($closeRevenueAmount);
            $isOppportunityChanged = true;
        }

        return $isOppportunityChanged;
    }
}
