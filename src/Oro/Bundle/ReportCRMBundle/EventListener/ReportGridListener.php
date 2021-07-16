<?php

namespace Oro\Bundle\ReportCRMBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmQueryConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Provider\State\DatagridStateProviderInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

/**
 * Changes data name of "period" filter according to filter value.
 */
class ReportGridListener
{
    const PERIOD_COLUMN_NAME          = 'period';
    const PERIOD_FILTER_DEFAULT_VALUE = 'monthPeriod';

    /** @var DatagridStateProviderInterface */
    private $filtersStateProvider;

    public function __construct(DatagridStateProviderInterface $filtersStateProvider)
    {
        $this->filtersStateProvider = $filtersStateProvider;
    }

    /**
     * Need to change data name depends to filter value
     *
     * Event: oro_datagrid.datagrid.build.before.oro_reportcrm-opportunities-won_by_period
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $filtersState = $this->filtersStateProvider->getState($config, $event->getDatagrid()->getParameters());
        $period = $filtersState[self::PERIOD_COLUMN_NAME]['value'] ?? self::PERIOD_FILTER_DEFAULT_VALUE;

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
        $aliasFields = ['monthPeriod', 'monthPeriodSorting', 'quarterPeriod', 'quarterPeriodSorting', 'yearPeriod'];
        foreach ($aliasFields as $index => $alias) {
            // skip current period alias and it's sorting helper alias
            if ($alias === $period || str_replace('Sorting', '', $alias) === $period) {
                continue;
            }

            $config->offsetUnsetByPath(sprintf('%s[%s]', OrmQueryConfiguration::SELECT_PATH, $index));
        }

        // and setup separate sorting column, used as well in grouping, but not affecting grouping result
        // period will be always the first column, unless changed in datagrids.yml
        // Use sorters configuration from datagrids.yml for "Period" = "All", not apply period sorting and grouping
        $groupAlias = '';
        $sortAlias = '';
        if ('yearPeriod' === $period) {
            $groupAlias = $period;
            $sortAlias = $period;
        } elseif ($period !== '') {
            $groupAlias = $period;
            $sortAlias  = sprintf('%sSorting', $period);
        }

        if ($groupAlias) {
            $config->getOrmQuery()->setGroupBy($groupAlias);
        }

        if ($sortAlias) {
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
}
