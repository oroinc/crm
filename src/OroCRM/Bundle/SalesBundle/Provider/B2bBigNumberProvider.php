<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class B2bBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /**
     * @param RegistryInterface   $doctrine
     * @param AclHelper           $aclHelper
     * @param BigNumberDateHelper $dateHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine   = $doctrine;
        $this->aclHelper  = $aclHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getNewLeadsCount($dateRange, $owners = [])
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getNewLeadsCount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getLeadsCount($dateRange, $owners = [])
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getLeadsCount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getNewOpportunitiesCount($dateRange, $owners = [])
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getNewOpportunitiesCount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getOpportunitiesCount(array $dateRange, $owners = [])
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getOpportunitiesCount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getTotalServicePipelineAmount(array $dateRange, $owners = [])
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getTotalServicePipelineAmount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return double
     */
    public function getOpenWeightedPipelineAmount($dateRange, $owners = [])
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getOpenWeightedPipelineAmount($this->aclHelper, $start, $end, $owners);
    }
}
