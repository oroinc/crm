<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
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

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     * @param BigNumberDateHelper $dateHelper
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->doctrine      = $doctrine;
        $this->aclHelper     = $aclHelper;
        $this->dateHelper    = $dateHelper;
        $this->qbTransformer = $qbTransformer;
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getNewLeadsCount($dateRange, $owners = [])
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Lead')
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
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Lead')
            ->getLeadsCount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getOpenLeadsCount($dateRange, $owners = [])
    {
        return $this->doctrine
            ->getRepository('OroSalesBundle:Lead')
            ->getOpenLeadsCount($this->aclHelper, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getNewOpportunitiesCount($dateRange, $owners = [])
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
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
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
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
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
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
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getOpenWeightedPipelineAmount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return double
     */
    public function getNewOpportunitiesAmount($dateRange, $owners = [])
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getNewOpportunitiesAmount($this->aclHelper, $start, $end, $owners);
    }


    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return int
     */
    public function getWonOpportunitiesToDateCount($dateRange, $owners = [])
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getWonOpportunitiesToDateCount($this->aclHelper, $start, $end, $owners);
    }

    /**
     * @param array $dateRange
     * @param int[] $owners
     *
     * @return double
     */
    public function getWonOpportunitiesToDateAmount($dateRange, $owners = [])
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroSalesBundle:Opportunity')
            ->getWonOpportunitiesToDateAmount($this->aclHelper, $this->qbTransformer, $start, $end, $owners);
    }
}
