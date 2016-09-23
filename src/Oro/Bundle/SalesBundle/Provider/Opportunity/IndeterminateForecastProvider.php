<?php

namespace Oro\Bundle\SalesBundle\Provider\Opportunity;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FilterProcessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class IndeterminateForecastProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var OwnerHelper */
    protected $ownerHelper;

    /** @var FilterProcessor */
    protected $filterProcessor;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var array */
    protected $data = [];

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     * @param OwnerHelper $ownerHelper
     * @param FilterProcessor $filterProcessor
     * @param NumberFormatter $numberFormatter
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        OwnerHelper $ownerHelper,
        FilterProcessor $filterProcessor,
        NumberFormatter $numberFormatter
    ) {
        $this->doctrine = $doctrine;
        $this->aclHelper = $aclHelper;
        $this->ownerHelper = $ownerHelper;
        $this->filterProcessor = $filterProcessor;
        $this->numberFormatter = $numberFormatter;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $dataKey
     *
     * @return string
     */
    public function getForecastOfOpportunitiesValues(WidgetOptionBag $widgetOptions, $dataKey)
    {
        $data = $this->getIndeterminateData(
            $this->ownerHelper->getOwnerIds($widgetOptions),
            $widgetOptions->get('queryFilter', [])
        );

        return [
            'value' => $this->numberFormatter->formatCurrency($data[$dataKey]),
        ];
    }

    /**
     * @param array
     * @param array
     *
     * @return ['totalIndeterminate' => <double>, 'weightedIndeterminate' => <double>]
     */
    public function getIndeterminateData(array $ownerIds, array $queryFilter = null)
    {
        $cacheKey = md5(serialize(func_get_args()));
        if (!isset($this->data[$cacheKey])) {
            $filters = isset($queryFilter['definition']['filters'])
                ? $queryFilter['definition']['filters']
                : [];

            $alias = 'o';
            $qb = $this->filterProcessor
                ->process(
                    $this->getOpportunityRepository()
                        ->getForecastQB($alias)
                        ->andWhere(sprintf('%s.closeDate IS NULL', $alias)),
                    'Oro\Bundle\SalesBundle\Entity\Opportunity',
                    $filters,
                    $alias
                );
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->andX(
                            sprintf('%s.probability <> 0', $alias),
                            sprintf('%s.probability <> 1', $alias)
                        ),
                        sprintf('%s.probability is NULL', $alias)
                    )
                );

            if (!empty($ownerIds)) {
                $qb->join('o.owner', 'owner');
                QueryUtils::applyOptimizedIn($qb, 'owner.id', $ownerIds);
            }

            $result = $this->aclHelper->apply($qb)->getOneOrNullResult()
                ?: ['budgetAmount' => 0, 'weightedForecast' => 0];

            $this->data[$cacheKey] = [
                'totalIndeterminate'    => $result['budgetAmount'],
                'weightedIndeterminate' => $result['weightedForecast'],
            ];
        }

        return $this->data[$cacheKey];
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->doctrine->getRepository('OroSalesBundle:Opportunity');
    }
}
