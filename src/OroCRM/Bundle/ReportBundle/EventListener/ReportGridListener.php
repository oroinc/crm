<?php

namespace OroCRM\Bundle\ReportBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

class ReportGridListener
{
    const PERIOD_COLUMN_NAME          = 'period';
    const PERIOD_FILTER_DEFAULT_VALUE = 'monthPeriod';

    /**
     * Need to change data name depends to filter value
     *
     * Event: oro_datagrid.datagrid.build.before.orocrm_report-opportunities-won_by_period
     *
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $filters = $event->getDatagrid()->getParameters()->get(OrmFilterExtension::FILTER_ROOT_PARAM, []);
        $period = isset($filters[self::PERIOD_COLUMN_NAME]['value'])
            ? $filters[self::PERIOD_COLUMN_NAME]['value']
            : self::PERIOD_FILTER_DEFAULT_VALUE;

        $config->offsetSetByPath(
            sprintf(
                '%s[%s][%s]',
                FilterConfiguration::COLUMNS_PATH,
                self::PERIOD_COLUMN_NAME,
                FilterUtility::DATA_NAME_KEY
            ),
            $period
        );
        $config->offsetSetByPath(
            sprintf(
                '[%s][%s][%s]',
                FormatterConfiguration::COLUMNS_KEY,
                self::PERIOD_COLUMN_NAME,
                PropertyInterface::DATA_NAME_KEY
            ),
            $period
        );

        // in order to meet sql standards on some RDBMS (e.g. Postgres)
        // unset columns not used in aggregation or group by statements
        $path        = '[source][query][select]';
        $aliasFields = ['monthPeriod', 'monthPeriodSorting', 'quarterPeriod', 'quarterPeriodSorting', 'yearPeriod'];

        foreach ($aliasFields as $index => $alias) {
            // skip current period alias and it's sorting helper alias
            if ($alias == $period || str_replace('Sorting', '', $alias) == $period) {
                continue;
            }

            $config->offsetUnsetByPath(sprintf('%s[%s]', $path, $index));
        }

        // and setup separate sorting column, used as well in grouping, but not affecting grouping result
        // period will be always the first column, unless changed in datagrid.yml
        if ($period == 'yearPeriod') {
            $groupAlias = $period;
            $sortAlias = $period;
        } else {
            $groupAlias = $period;
            $sortAlias  = sprintf('%sSorting', $period);
        }

        $config->offsetSetByPath('[source][query][groupBy]', $groupAlias);

        $config->offsetSetByPath(
            sprintf(
                '%s[%s][%s]',
                OrmSorterConfiguration::COLUMNS_PATH,
                self::PERIOD_COLUMN_NAME,
                PropertyInterface::DATA_NAME_KEY
            ),
            $sortAlias
        );
    }
}
