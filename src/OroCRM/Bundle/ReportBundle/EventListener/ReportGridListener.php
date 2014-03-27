<?php

namespace OroCRM\Bundle\ReportBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

class ReportGridListener
{
    const PERIOD_COLUMN_NAME          = 'period';
    const PERIOD_FILTER_DEFAULT_VALUE = 'monthPeriod';

    /** @var \Oro\Bundle\DataGridBundle\Datagrid\RequestParameters */
    protected $params;

    public function __construct(RequestParameters $params)
    {
        $this->params = $params;
    }

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

        $filters = $this->params->get(OrmFilterExtension::FILTER_ROOT_PARAM);
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
                '%s[%s][%s]',
                OrmSorterConfiguration::COLUMNS_PATH,
                self::PERIOD_COLUMN_NAME,
                PropertyInterface::DATA_NAME_KEY
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
    }
}
