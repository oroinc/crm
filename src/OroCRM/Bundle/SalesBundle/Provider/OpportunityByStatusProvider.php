<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Filter\DateRangeFilter;
use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Utils\DateFilterModifier;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class OpportunityByStatusProvider
{
    /** @var RegistryInterface */
    protected $registry;
    
    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateRangeFilter */
    protected $dateFilter;

    /** @var DateFilterModifier */
    protected $modifier;

    /**
     * @param RegistryInterface  $doctrine
     * @param AclHelper          $aclHelper
     * @param DateRangeFilter    $filter
     * @param DateFilterModifier $modifier
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        DateRangeFilter $filter,
        DateFilterModifier $modifier
    ) {
        $this->registry   = $doctrine;
        $this->aclHelper  = $aclHelper;
        $this->dateFilter = $filter;
        $this->modifier   = $modifier;
    }

    /**
     * @param array $dateRange
     * @param array $statusesData
     *
     * @return array
     */
    public function getOpportunitiesGroupedByStatus(array $dateRange, array $statusesData)
    {
        foreach ($statusesData as $key => $name) {
            $resultData[$key] = [
                'name'   => $key,
                'label'  => $name,
                'budget' => 0,
            ];
        }
        $qb                 = $this->getOpportunityRepository()->getGroupedOpportunitiesByStatusQB('o');
        $adapter            = new OrmFilterDatasourceAdapter($qb);
        $start              = $dateRange['start'] instanceof \DateTime
            ? $dateRange['start']->format('Y-m-d H:i')
            : $dateRange['start'];
        $end                = $dateRange['end'] instanceof \DateTime
            ? $dateRange['end']->format('Y-m-d H:i')
            : $dateRange['end'];
        $dateRange['value'] = [
            'start' => $start,
            'end'   => $end
        ];
        unset($dateRange['start'], $dateRange['end']);
        $dateData = $this->modifier->modify($dateRange);
        $this->dateFilter->init('datetime', [FilterUtility::DATA_NAME_KEY => 'o.createdAt']);
        $this->dateFilter->apply($adapter, $dateData);
        $groupedData = $this->aclHelper->apply($qb)->getArrayResult();

        $resultData = [];
        foreach ($groupedData as $statusData) {
            $status = $statusData['name'];
            $budget = (float)$statusData['budget'];
            if ($budget) {
                $resultData[$status]['budget'] = $budget;
            }
        }

        return $resultData;
    }

    /**
     * @return OpportunityRepository
     */
    protected function getOpportunityRepository()
    {
        return $this->registry->getRepository('OroCRMSalesBundle:Opportunity');
    }
}
