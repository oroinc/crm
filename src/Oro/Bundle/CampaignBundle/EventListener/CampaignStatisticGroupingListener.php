<?php

namespace Oro\Bundle\CampaignBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\QueryDesignerBundle\Model\GroupByHelper;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;

class CampaignStatisticGroupingListener
{
    const PATH_GROUPBY = '[source][query][groupBy]';
    /** @deprecated since 1.10. Use config->getName() instead */
    const PATH_NAME = '[name]';
    const PATH_SELECT = '[source][query][select]';

    const MIXIN_NAME = 'orocrm-email-campaign-marketing-list-items-mixin';
    const MANUAL_MIXIN_NAME = 'orocrm-email-campaign-marketing-list-manual-items-mixin';

    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @var GroupByHelper
     */
    protected $groupByHelper;

    /**
     * @param MarketingListHelper $marketingListHelper
     * @param GroupByHelper $groupByHelper
     */
    public function __construct(MarketingListHelper $marketingListHelper, GroupByHelper $groupByHelper)
    {
        $this->marketingListHelper = $marketingListHelper;
        $this->groupByHelper = $groupByHelper;
    }

    /**
     * Add fields that are not mentioned in aggregate functions to GROUP BY.
     *
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();
        $parameters = $event->getParameters();

        if (!$this->isApplicable($config->getName(), $parameters)) {
            return;
        }

        $selects = $config->offsetGetByPath(self::PATH_SELECT, []);
        $groupBy = $config->offsetGetByPath(self::PATH_GROUPBY);

        $groupBy = $this->groupByHelper->getGroupByFields($groupBy, $selects);
        if ($groupBy) {
            $config->offsetSetByPath(self::PATH_GROUPBY, implode(',', $groupBy));
        }
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
