<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25_4;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Component\PhpUtils\ArrayUtil;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FixReportsQuery extends ParametrizedMigrationQuery
{
    const LIMIT = 100;

    /** @var array */
    protected $fixes = [
        'filters' => [
            'Oro\Bundle\SalesBundle\Entity\Opportunity' => 'status',
            'Oro\Bundle\SalesBundle\Entity\Lead' => 'status',
        ],
        'removedFields' => [
            'status+Oro\Bundle\SalesBundle\Entity\OpportunityStatus::label' => 'status',
            'status+Oro\Bundle\SalesBundle\Entity\OpportunityStatus::name' => 'status',
            'status+Oro\Bundle\SalesBundle\Entity\LeadStatus::label' => 'status',
            'status+Oro\Bundle\SalesBundle\Entity\LeadStatus::name' => 'status',
        ],
        'chainFilters' => [
            'Oro\Bundle\SalesBundle\Entity\Opportunity::status',
            'Oro\Bundle\SalesBundle\Entity\Lead::status',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $tables = ['oro_segment', 'oro_report'];
        foreach ($tables as $table) {
            $this->updateRows($logger, $table, $dryRun);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param string          $table
     * @param bool            $dryRun
     */
    protected function updateRows(LoggerInterface $logger, $table, $dryRun = false)
    {
        $steps = ceil($this->getCount($table) / static::LIMIT);

        $reportQb = $this->createQb($table)
            ->setMaxResults(static::LIMIT);

        for ($i = 0; $i < $steps; $i++) {
            $rows = $reportQb
                ->setFirstResult($i * static::LIMIT)
                ->execute()
                ->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                if ($this->processRow($row)) {
                    $this->saveChanges($logger, $table, $row, $dryRun);
                }
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param string          $table
     * @param array           $row
     * @param bool            $dryRun
     */
    protected function saveChanges(LoggerInterface $logger, $table, array $row, $dryRun)
    {
        $query = <<<SQL
UPDATE $table
SET definition = :definition
WHERE id = :id
SQL;
        $params = [
            'id'         => $row['id'],
            'definition' => $row['definition'],
        ];

        $this->logQuery($logger, $query, $params);
        if (!$dryRun) {
            $this->connection->executeStatement($query, $params);
        }
    }

    /**
     * @param array $row
     *
     * @return bool True if there are changes, false otherwise
     */
    protected function processRow(array &$row)
    {
        $isChanged = false;
        $def = json_decode($row['definition'], true);
        if (isset($def['columns'])) {
            if ($this->fixColumns($def)) {
                $isChanged = true;
            }
        }

        if (isset($def['filters'])) {
            if ($this->fixFilters($row, $def)) {
                $isChanged = true;
            }
        }

        if (isset($def['grouping_columns'])) {
            if ($this->fixGroupingColumns($def)) {
                $isChanged = true;
            }
        }

        if ($isChanged) {
            $this->removeDuplicates($def);
            $row['definition'] = json_encode($def);
        }

        return $isChanged;
    }

    /**
     * If you add same field twice in "Columns" section in query designer,
     * you'll get exception that alias is already defined
     *
     * Also there is no point in having duplicated stuff
     */
    protected function removeDuplicates(array &$def)
    {
        if (!isset($def['columns'])) {
            return;
        }

        $existingColumns = [];
        foreach ($def['columns'] as $key => $column) {
            $id = implode('.', [$column['name'], $column['func']]);
            if (isset($existingColumns[$id])) {
                unset($def['columns'][$key]);
            } else {
                $existingColumns[$id] = $key;
            }
        }

        $def['columns'] = array_values($def['columns']);
    }

    /**
     * @param array $def
     *
     * @return bool
     */
    protected function fixColumns(array &$def)
    {
        $isChanged = false;
        if (!isset($def['columns'])) {
            return $isChanged;
        }

        foreach ($def['columns'] as &$column) {
            if ($this->fixRemovedField($column, 'name')) {
                $isChanged = true;
            }
        }

        return $isChanged;
    }

    /**
     * @param array $row
     * @param array $def
     *
     * @return bool
     */
    protected function fixFilters(array $row, array &$def)
    {
        $isChanged = false;
        foreach ($def['filters'] as $key => $filter) {
            $this->fixRemovedField($filter, 'columnName');
            if (!$this->isNeedToUpdateEnumFilter($row, $filter)) {
                continue;
            }

            $isChanged = true;
            $value = $this->filterValue($filter);
            $stringType = isset($filter['criterion']['data']['type'])
                ? (int) $filter['criterion']['data']['type']
                : null;
            // If string operator expects operand to be array
            // parse value off the db representation
            if (in_array($stringType, [6, 7], true)) {
                $value = explode(',', $value);
            }
            // As enum operators expects operand to be always array
            // as opposed to string operators, make sure it is array
            if (!is_array($value)) {
                $value = [$value];
            }

            $entityNameParts = explode('\\', $row['entity']);
            $def['filters'][$key] = [
                'columnName' => $filter['columnName'],
                'criterion' => [
                    'filter' => 'enum',
                    'data' => [
                        'type' => $this->getEnumType($stringType),
                        'value' => $value,
                        'params' => [
                            'class' => sprintf('Extend\Entity\EV_%s_Status', end($entityNameParts)),
                        ],
                    ],
                ],
            ];
        }

        return $isChanged;
    }

    /**
     * @param mixed $config
     *
     * @return bool
     */
    public function fixRemovedField(&$config, $key)
    {
        if (!isset($config[$key])) {
            return false;
        }

        $search = ArrayUtil::find(
            function ($column) use ($config, $key) {
                return preg_match(sprintf('/%s$/', preg_quote($column)), $config[$key]);
            },
            array_keys($this->fixes['removedFields'])
        );

        if (!$search) {
            return false;
        }

        $config[$key] = str_replace($search, $this->fixes['removedFields'][$search], $config[$key]);

        return true;
    }

    /**
     * @param mixed $filter
     *
     * @return mixed
     */
    protected function filterValue($filter)
    {
        return isset($filter['criterion']['data']['value']) ? $filter['criterion']['data']['value'] : '';
    }

    /**
     * @param array $def
     *
     * @return bool
     */
    protected function fixGroupingColumns(array &$def)
    {
        $isChanged = false;
        foreach ($def['grouping_columns'] as &$group) {
            if ($this->fixRemovedField($group, 'name')) {
                $isChanged = true;
            }
        }

        return $isChanged;
    }

    /**
     * @param array $row
     * @param mixed $filter
     *
     * @return bool
     */
    protected function isNeedToUpdateEnumFilter(array $row, $filter)
    {
        if (!isset($filter['columnName'], $filter['criterion']['filter'])
            || $filter['criterion']['filter'] !== 'string'
        ) {
            return false;
        }

        if (isset($this->fixes['filters'][$row['entity']])
            && $filter['columnName'] === $this->fixes['filters'][$row['entity']]
        ) {
            return true;
        }

        return ArrayUtil::some(
            function ($column) use ($filter) {
                return preg_match(sprintf('/%s$/', preg_quote($column)), $filter['columnName']);
            },
            $this->fixes['chainFilters']
        );
    }

    /**
     * Converts string operators to enum operators
     *
     * @param int $stringType
     *
     * @return string
     */
    protected function getEnumType($stringType)
    {
        return in_array($stringType, [1, 3, 4, 5, 6], true) ? '1' : '2';
    }

    /**
     * @return int
     */
    protected function getCount($table)
    {
        return $this->createQb($table)
            ->select('COUNT(1)')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function createQb($table)
    {
        return $this->connection->createQueryBuilder()
            ->select('r.id AS id, r.entity AS entity, r.definition AS definition')
            ->from($table, 'r');
    }
}
