<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository;

class OpportunityByStatusProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /**
     * @param RegistryInterface   $doctrine
     * @param AclHelper           $aclHelper
     * @param DateFilterProcessor $processor
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $processor
    ) {
        $this->registry            = $doctrine;
        $this->aclHelper           = $aclHelper;
        $this->dateFilterProcessor = $processor;
    }

    /**
     * @param array $dateRange
     * @param array $statusesData
     *
     * @return array
     */
    public function getOpportunitiesGroupedByStatus(array $dateRange, array $statusesData)
    {
        $resultData = [];
        foreach ($statusesData as $key => $name) {
            $resultData[$key] = [
                'name'   => $key,
                'label'  => $name,
                'budget' => 0,
            ];
        }
        $qb = $this->getOpportunityRepository()->getGroupedOpportunitiesByStatusQB('o');
        $this->dateFilterProcessor->process($qb, $dateRange, 'o.createdAt');
        $groupedData = $this->aclHelper->apply($qb)->getArrayResult();

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
