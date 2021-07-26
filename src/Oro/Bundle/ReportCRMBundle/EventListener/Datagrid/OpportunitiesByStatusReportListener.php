<?php

namespace Oro\Bundle\ReportCRMBundle\EventListener\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FilterBundle\Filter\DateFilterUtility;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\FilterBundle\Utils\DateFilterModifier;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Apply query modifications to the Opportunity By Status Report
 * Add enum status class name to the FROM clause
 * Apply the defined date and datetime filters to JOIN instead of WHERE
 */
class OpportunitiesByStatusReportListener
{
    /**
     * @var array Map of date filters and comparison operators
     */
    public static $comparatorsMap = [
        AbstractDateFilterType::TYPE_LESS_THAN => '<',
        AbstractDateFilterType::TYPE_MORE_THAN => '>',
        AbstractDateFilterType::TYPE_EQUAL => '=',
        AbstractDateFilterType::TYPE_NOT_EQUAL => '<>',
        AbstractDateFilterType::TYPE_BETWEEN => ['>=', 'AND', '<='],
        AbstractDateFilterType::TYPE_NOT_BETWEEN => ['<', 'OR', '>'],
    ];

    /** @var DateFilterModifier */
    protected $dateFilterModifier;

    /** @var DateFilterUtility */
    protected $dateFilterUtility;

    /**
     * OpportunitiesByStatusReportListener constructor.
     */
    public function __construct(
        DateFilterModifier $dateFilterModifier,
        DateFilterUtility $dateFilterUtility
    ) {
        $this->dateFilterModifier = $dateFilterModifier;
        $this->dateFilterUtility = $dateFilterUtility;
    }

    /**
     * event: oro_datagrid.datagrid.build.before.oro_reportcrm-opportunities-by_status
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $event->getConfig()->getOrmQuery()
            ->resetFrom()
            ->addFrom(ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE), 'status');
    }

    /**
     * Move the date filters into join clause to avoid filtering statuses from the report
     *
     * event: oro_datagrid.datagrid.build.after.oro_reportcrm-opportunities-by_status
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $dataGrid = $event->getDatagrid();
        $dataSource = $dataGrid->getDatasource();
        if (!$dataSource instanceof OrmDatasource) {
            return;
        }

        $queryBuilder = $dataSource->getQueryBuilder();

        $joinConditions = [];
        $filters = $dataGrid->getParameters()->get('_filter');
        if (!$filters) {
            return;
        }

        $filtersConfig = $dataGrid->getConfig()->offsetGetByPath('[filters][columns]');

        // create a map of join filter conditions
        foreach ($filtersConfig as $key => $config) {
            $fieldName = $config[FilterUtility::DATA_NAME_KEY];
            $filterType = $config['type'];
            // get date and datetime filters only
            if (\array_key_exists($key, $filters)
                && \in_array($filterType, ['date', 'datetime'])
                && str_contains($fieldName, '.')
            ) {
                [$alias, $field] = explode('.', $fieldName);
                // build a join clause
                $dateCondition = $this->buildDateCondition($filters[$key], $fieldName, $filterType, $queryBuilder);
                if ($dateCondition) {
                    $joinConditions[$alias][$field][] = $dateCondition;
                }
                // remove filters so it does not appear in the where clause
                unset($filters[$key]);
            }
        }

        // update filter params (without removed ones)
        $dataGrid->getParameters()->set('_filter', $filters);

        // Prepare new join
        $joinParts = $queryBuilder->getDQLPart('join');

        $queryBuilder->resetDQLPart('join');

        // readd join parts and append filter conditions to the appropriate joins
        foreach ($joinParts as $joins) {
            /** @var \Doctrine\ORM\Query\Expr\Join $join */
            foreach ($joins as $join) {
                $alias = $join->getAlias();
                $fieldCondition = '';
                // check if there is a column with a join filter on this alias
                if (array_key_exists($alias, $joinConditions)) {
                    foreach ($joinConditions[$alias] as $fieldConditions) {
                        $fieldCondition .= implode($fieldConditions);
                    }
                }
                $queryBuilder->leftJoin(
                    $join->getJoin(),
                    $alias,
                    $join->getConditionType(),
                    $join->getCondition() . $fieldCondition,
                    $join->getIndexBy()
                );
            }
        }
    }

    /**
     * Generates SQL date comparison string depending on filter $options
     * Returns false if date filter options are invalid
     *
     * @param array $options Filter options
     * @param string $fieldName
     * @param string $filterType date filter type ('date' or 'datetime')
     * @param QueryBuilder $queryBuilder
     *
     * @return string|bool
     */
    protected function buildDateCondition(array $options, $fieldName, $filterType, QueryBuilder $queryBuilder)
    {
        // apply variables and normalize
        $data = $this->dateFilterModifier->modify($options);

        $data['value']['start_original'] = $options['value']['start'];
        $data['value']['end_original'] = $options['value']['end'];

        $data = $this->dateFilterUtility->parseData($fieldName, $data, $filterType);

        if (!$data || (empty($data['date_start']) && empty($data['date_end']))) {
            return false;
        }

        $field = $data['field'];
        $type = $data['type'];

        if (!array_key_exists($type, static::$comparatorsMap)) {
            return false;
        }

        $comparator = static::$comparatorsMap[$type];

        // date range comparison
        if (is_array($comparator)) {
            $paramStart = QueryBuilderUtil::generateParameterName($fieldName);
            $queryBuilder->setParameter($paramStart, $data['date_start']);
            $paramEnd = QueryBuilderUtil::generateParameterName($fieldName);
            $queryBuilder->setParameter($paramEnd, $data['date_end']);

            return sprintf(
                ' AND (%s %s %s)',
                $this->formatComparison($field, $comparator[0], $paramStart),
                $comparator[1],
                $this->formatComparison($field, $comparator[2], $paramEnd)
            );
        }

        $value = !empty($data['date_start']) ? $data['date_start'] : $data['date_end'];
        // simple date comparison
        $param = QueryBuilderUtil::generateParameterName($fieldName);
        $queryBuilder->setParameter($param, $value);

        return sprintf(' AND (%s)', $this->formatComparison($field, $comparator, $param));
    }

    /**
     * Generates a comparison string
     *
     * @param string $fieldName
     * @param string $operator
     * @param string $parameterName
     *
     * @return string
     */
    protected function formatComparison($fieldName, $operator, $parameterName)
    {
        return sprintf('%s %s :%s', $fieldName, $operator, $parameterName);
    }
}
