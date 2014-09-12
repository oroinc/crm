<?php

namespace OroCRM\Bundle\CampaignBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSegmentHelper;

class CampaignStatisticDatagridListener
{
    const PATH_GROUPBY = '[source][query][groupBy]';

    const MIXIN_NAME = 'orocrm-email-campaign-marketing-list-items-mixin';

    /**
     * @var MarketingListSegmentHelper
     */
    protected $segmentHelper;

    /**
     * @param MarketingListSegmentHelper $segmentHelper
     */
    public function __construct(MarketingListSegmentHelper $segmentHelper)
    {
        $this->segmentHelper = $segmentHelper;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config     = $event->getConfig();
        $parameters = $event->getParameters();
        $gridName   = $config->offsetGetByPath('[name]');

        if (!$this->isApplicable($gridName, $parameters)) {
            return;
        }

        $selects = $config->offsetGetByPath('[source][query][select]', []);
        $groupBy = [];
        foreach ($selects as $select) {
            preg_match('/(.+)\sas\sc\d$/i', $select, $parts);

            if (!empty($parts[1])) {
                $groupBy[] = $parts[1];
            }
        }

        if (empty($groupBy)) {
            return;
        }

        if ($existingGroupBy = $config->offsetGetByPath(self::PATH_GROUPBY)) {
            $groupBy[] = $existingGroupBy;
        }

        $config->offsetSetByPath(self::PATH_GROUPBY, implode(', ', $groupBy));
    }

    /**
     * @param string       $gridName
     * @param ParameterBag $parameterBag
     *
     * @return bool
     */
    public function isApplicable($gridName, ParameterBag $parameterBag)
    {
        if ($parameterBag->get(MarketingListItemsListener::MIXIN, false) !== self::MIXIN_NAME) {
            return false;
        }

        $segmentId = $this->segmentHelper->getSegmentIdByGridName($gridName);

        return $segmentId && (bool)$this->segmentHelper->getMarketingListBySegment($segmentId);
    }
}
