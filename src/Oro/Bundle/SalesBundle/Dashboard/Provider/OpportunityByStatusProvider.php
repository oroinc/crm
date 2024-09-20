<?php

namespace Oro\Bundle\SalesBundle\Dashboard\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumOptionRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provides chart data for 'Opportunity By Status' dashboard widget.
 */
class OpportunityByStatusProvider
{
    public function __construct(
        protected ManagerRegistry $registry,
        protected AclHelper $aclHelper,
        protected WidgetProviderFilterManager $widgetProviderFilter,
        protected DateFilterProcessor $dateFilterProcessor,
        protected CurrencyQueryBuilderTransformerInterface $qbTransformer,
        protected TranslatorInterface $translator
    ) {
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     *
     * @return array
     */
    public function getOpportunitiesGroupedByStatus(WidgetOptionBag $widgetOptions)
    {
        $dateRange = $widgetOptions->get('dateRange');

        /**
         * Excluded statuses will be filtered from result in method `formatResult` below.
         * Due to performance issues with `NOT IN` clause in database.
         */
        $excludedStatuses = $widgetOptions->get('excluded_statuses', []);
        $orderBy = $widgetOptions->get('useQuantityAsData') ? 'quantity' : 'budget';

        /** @var OpportunityRepository $opportunityRepository */
        $opportunityRepository = $this->registry->getRepository(Opportunity::class);
        $qb = $opportunityRepository->createQueryBuilder('o')
            ->select("JSON_EXTRACT(o.serialized_data, 'status') as status")
            ->groupBy('status')
            ->orderBy($orderBy, 'DESC');

        switch ($orderBy) {
            case 'quantity':
                $qb->addSelect('COUNT(o.id) as quantity');
                break;
            case 'budget':
                $closeRevenueQuery = $this->qbTransformer->getTransformSelectQuery('closeRevenue', $qb, 'o');
                $budgetAmountQuery = $this->qbTransformer->getTransformSelectQuery('budgetAmount', $qb, 'o');
                $qb->addSelect(sprintf(
                    'SUM(
                        CASE WHEN JSON_EXTRACT(o.serialized_data, \'status\') = \'opportunity_status.won\'
                            THEN (CASE WHEN o.closeRevenueValue IS NOT NULL THEN (%1$s) ELSE 0 END)
                            ELSE (CASE WHEN o.budgetAmountValue IS NOT NULL THEN (%2$s) ELSE 0 END)
                        END
                    ) as budget',
                    $closeRevenueQuery,
                    $budgetAmountQuery
                ));
        }

        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'o.createdAt');
        $this->widgetProviderFilter->filter($qb, $widgetOptions);
        $result = $this->aclHelper->apply($qb)->getArrayResult();

        return $this->formatResult($result, $excludedStatuses, $orderBy);
    }

    /**
     * @param array $result
     * @param string[] $excludedStatuses
     * @param string $orderBy
     *
     * @return array
     */
    protected function formatResult($result, $excludedStatuses, $orderBy)
    {
        $resultStatuses = [];
        foreach ($result as $resultIndex => $item) {
            $status = $item['status'];
            if ($status) {
                $resultStatuses[$status] = $resultIndex;
            }
        }

        foreach ($this->getAvailableOpportunityStatuses() as $statusKey => $statusLabel) {
            $resultIndex = $resultStatuses[$statusKey] ?? null;
            if (in_array($statusKey, $excludedStatuses)) {
                if (null !== $resultIndex) {
                    unset($result[$resultIndex]);
                }
                continue;
            }

            if (null !== $resultIndex) {
                $result[$resultIndex]['label'] = $statusLabel;
            } else {
                $result[] = [
                    'status' => $statusKey,
                    'label' => $statusLabel,
                    $orderBy => 0
                ];
            }
        }

        return $result;
    }

    protected function getAvailableOpportunityStatuses(): array
    {
        /** @var EnumOptionRepository $statusesRepository */
        $statusesRepository = $this->registry->getRepository(EnumOption::class);
        $statuses = $statusesRepository->createQueryBuilder('s')
            ->select('s.id')
            ->andWhere('s.enumCode = :enumCode')
            ->setParameter('enumCode', Opportunity::INTERNAL_STATUS_CODE)
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($statuses as $status) {
            $result[$status['id']] =  $this->translator->trans(
                ExtendHelper::buildEnumOptionTranslationKey($status['id'])
            );
        }

        return $result;
    }
}
