<?php

namespace OroCRM\Bundle\CampaignBundle\EventListener;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\EventListener\MixinListener;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class CampaignStatisticDatagridListener
{
    const PATH_NAME = '[name]';
    const PATH_DATAGRID_WHERE = '[source][query][where]';

    const MIXIN_SENT_NAME = 'orocrm-email-campaign-marketing-list-sent-items-mixin';
    const MIXIN_UNSENT_NAME = 'orocrm-email-campaign-marketing-list-unsent-items-mixin';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @param MarketingListHelper $marketingListHelper
     * @param ManagerRegistry $registry
     */
    public function __construct(MarketingListHelper $marketingListHelper, ManagerRegistry $registry)
    {
        $this->marketingListHelper = $marketingListHelper;
        $this->registry = $registry;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();
        $parameters = $event->getParameters();
        $gridName = $config->offsetGetByPath(self::PATH_NAME);

        if (!$this->isApplicable($gridName, $parameters)) {
            return;
        }

        $emailCampaignId = $parameters->get('emailCampaign');
        $emailCampaign = $this->registry->getRepository('OroCRMCampaignBundle:EmailCampaign')
            ->find($emailCampaignId);

        if ($emailCampaign->isSent()) {
            $config->offsetUnsetByPath(self::PATH_DATAGRID_WHERE);
            $mixin = self::MIXIN_SENT_NAME;
        } else {
            $mixin = self::MIXIN_UNSENT_NAME;
        }

        $parameters->set(MixinListener::GRID_MIXIN, $mixin);
    }

    /**
     * This listener is applicable for marketing list grids that has emailCampaign parameter set.
     *
     * @param string $gridName
     * @param ParameterBag $parameterBag
     *
     * @return bool
     */
    public function isApplicable($gridName, ParameterBag $parameterBag)
    {
        if (!$parameterBag->has('emailCampaign')) {
            return false;
        }

        return (bool)$this->marketingListHelper->getMarketingListIdByGridName($gridName);
    }
}
