<?php

namespace Oro\Bundle\CampaignBundle\Dashboard;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;

class CampaignDataProvider
{
    const CAMPAIGN_LEAD_COUNT          = 5;
    const CAMPAIGN_OPPORTUNITY_COUNT   = 5;
    const CAMPAIGN_CLOSE_REVENUE_COUNT = 5;

    /** @var RegistryInterface */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    /**
     * @param RegistryInterface $doctrine
     * @param AclHelper $aclHelper
     * @param DateFilterProcessor $processor
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $processor,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->registry            = $doctrine;
        $this->aclHelper           = $aclHelper;
        $this->dateFilterProcessor = $processor;
        $this->qbTransformer       = $qbTransformer;
    }

    /**
     * @param array $dateRange
     *
     * @return array
     */
    public function getCampaignLeadsData(array $dateRange)
    {
        $qb = $this->getCampaignRepository()->getCampaignsLeadsQB('lead');
        $qb->setMaxResults(self::CAMPAIGN_LEAD_COUNT);
        $this->dateFilterProcessor->process($qb, $dateRange, 'lead.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param array $dateRange
     *
     * @return array
     */
    public function getCampaignOpportunitiesData(array $dateRange)
    {
        $qb = $this->getCampaignRepository()->getCampaignsOpportunitiesQB('opportunities');
        $qb->setMaxResults(self::CAMPAIGN_OPPORTUNITY_COUNT);
        $this->dateFilterProcessor->process($qb, $dateRange, 'opportunities.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param array $dateRange
     *
     * @return array
     */
    public function getCampaignsByCloseRevenueData(array $dateRange)
    {
        $qb = $this->getCampaignRepository()->getCampaignsByCloseRevenueQB(
            'opportunities',
            $this->qbTransformer
        );
        $qb->setMaxResults(self::CAMPAIGN_CLOSE_REVENUE_COUNT);
        $this->dateFilterProcessor->process($qb, $dateRange, 'opportunities.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @return CampaignRepository
     */
    protected function getCampaignRepository()
    {
        return $this->registry->getRepository('OroCampaignBundle:Campaign');
    }
}
