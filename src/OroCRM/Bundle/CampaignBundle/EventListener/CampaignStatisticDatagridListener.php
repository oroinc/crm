<?php

namespace OroCRM\Bundle\CampaignBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListHelper;

class CampaignStatisticDatagridListener
{
    const PATH_GROUPBY = '[source][query][groupBy]';

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
     * Add fields that are not mentioned in aggregate functions to GROUP BY.
     *
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
            // Do not add fields with aggregate functions
            preg_match('/(MIN|MAX|AVG|COUNT|SUM)\(/i', $select, $matches);
            if ($matches) {
                continue;
            }

            // Search for field alias if applicable or field name to use in group by
            preg_match('/([^\s]+)\s+as\s+(\w+)$/i', $select, $parts);
            if (!empty($parts[2])) {
                // Add alias
                $groupBy[] = $parts[2];
            } elseif (!$parts) {
                // Add field itself when there is no alias
                $groupBy[] = $select;
            }
        }

        if (empty($groupBy)) {
            return;
        }

        // Add existing group by statement to group by list
        if ($existingGroupBy = $config->offsetGetByPath(self::PATH_GROUPBY)) {
            $groupBy[] = $existingGroupBy;
        }

        // Update group by fields list
        $config->offsetSetByPath(self::PATH_GROUPBY, implode(', ', $groupBy));
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid   = $event->getDatagrid();
        $parameters = $datagrid->getParameters();

        if (!$this->isApplicable($datagrid->getName(), $parameters)) {
            return;
        }

        if (!$emailCampaign = $parameters->get('emailCampaign', false)) {
            throw new \InvalidArgumentException('Parameter "emailCampaign" is missing');
        }

        $datasource = $datagrid->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            $datasource
                ->getQueryBuilder()
                ->setParameter('emailCampaign', $emailCampaign);
        }
    }

    /**
     * @param string       $gridName
     * @param ParameterBag $parameterBag
     *
     * @return bool
     */
    public function isApplicable($gridName, ParameterBag $parameterBag)
    {
        $gridMixin = $parameterBag->get(MarketingListItemsListener::MIXIN, false);
        if ($gridMixin !== self::MIXIN_NAME && $gridMixin !== self::MANUAL_MIXIN_NAME) {
            return false;
        }

        return (bool)$this->marketingListHelper->getMarketingListIdByGridName($gridName);
    }
}
