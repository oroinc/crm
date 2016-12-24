<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\MagentoBundle\Provider\ChannelType;
use Oro\Bundle\MagentoBundle\Provider\DateFilterTrait;
use Oro\Bundle\MagentoBundle\Provider\WebsiteVisitProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class WebsiteVisitProvider implements WebsiteVisitProviderInterface, FeatureToggleableInterface
{
    use DateFilterTrait, FeatureCheckerHolderTrait;

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
     * @inheritdoc
     */
    public function getSiteVisitsValues($dateRange)
    {
        if (!$this->isFeaturesEnabled()) {
            return 0;
        }

        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(ChannelType::TYPE);
        if (!$visitsQb instanceof QueryBuilder) {
            return 0;
        }

        list($start, $end) = $this->dateHelper->getPeriod(
            $dateRange,
            'OroTrackingBundle:TrackingVisit',
            'firstActionTime'
        );
        $this->applyDateFiltering($visitsQb, 'visit.firstActionTime', $start, $end);

        return (int) $this->aclHelper->apply($visitsQb)->getSingleScalarResult();
    }

    /**
     * @return ChannelRepository
     */
    protected function getChannelRepository()
    {
        return $this->doctrine->getRepository('OroChannelBundle:Channel');
    }
}
