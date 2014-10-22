<?php

namespace OroCRM\Bundle\CampaignBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class CampaignStatisticDatagridListener
{
    const PATH_GROUPBY = '[source][query][groupBy]';
    const PATH_NAME = '[name]';
    const PATH_SELECT = '[source][query][select]';

    const MIXIN_NAME = 'orocrm-email-campaign-marketing-list-items-mixin';
    const MANUAL_MIXIN_NAME = 'orocrm-email-campaign-marketing-list-manual-items-mixin';

    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @param MarketingListHelper $marketingListHelper
     */
    public function __construct(MarketingListHelper $marketingListHelper)
    {
        $this->marketingListHelper = $marketingListHelper;
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

        $this->fixBrokenDataGridGroupBy($config);
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

    /**
     * Add fields that are not mentioned in aggregate functions to GROUP BY.
     *
     * @param DatagridConfiguration $config
     */
    protected function fixBrokenDataGridGroupBy(DatagridConfiguration $config)
    {
        $selects = $config->offsetGetByPath(self::PATH_SELECT, []);
        $groupBy = $config->offsetGetByPath(self::PATH_GROUPBY);

        $groupBy = $this->getGroupByFields($groupBy, $selects);
        if ($groupBy) {
            $config->offsetSetByPath(self::PATH_GROUPBY, implode(',', $groupBy));
        }
    }

    /**
     * Get fields that must appear in GROUP BY.
     *
     * @param string|array $groupBy
     * @param array $selects
     * @return array
     */
    protected function getGroupByFields($groupBy, $selects)
    {
        $groupBy = $this->getPreparedGroupBy($groupBy);

        foreach ($selects as $select) {
            $select = trim((string)$select);
            // Do not add fields with aggregate functions
            if ($this->hasAggregate($select)) {
                continue;
            }

            if ($field = $this->getFieldForGroupBy($select)) {
                $groupBy[] = $field;
            }
        }

        return array_unique($groupBy);
    }

    /**
     * Get GROUP BY statements as array of trimmed parts.
     *
     * @param string|array $groupBy
     * @return array
     */
    protected function getPreparedGroupBy($groupBy)
    {
        if (!is_array($groupBy)) {
            $groupBy = explode(',', $groupBy);
        }

        $result = [];
        foreach ($groupBy as $groupByPart) {
            $groupByPart = trim((string)$groupByPart);
            if ($groupByPart) {
                $result[] = $groupByPart;
            }
        }

        return $result;
    }

    /**
     * @param string $select
     * @return bool
     */
    protected function hasAggregate($select)
    {
        preg_match('/(MIN|MAX|AVG|COUNT|SUM)\(/i', $select, $matches);

        return (bool)$matches;
    }

    /**
     * Search for field alias if applicable or field name to use in group by
     *
     * @param string $select
     * @return string|null
     */
    protected function getFieldForGroupBy($select)
    {
        preg_match('/([^\s]+)\s+as\s+(\w+)$/i', $select, $parts);
        if (!empty($parts[2])) {
            // Add alias
            return $parts[2];
        } elseif (!$parts && strpos($select, ' ') === false) {
            // Add field itself when there is no alias
            return $select;
        }

        return null;
    }
}
