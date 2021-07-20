<?php

namespace Oro\Bundle\ActivityContactBundle\Migration;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Add contact activity related columns to entities that has configured Contact Activity relation. Columns:
 *  - ac_last_contact_date
 *  - ac_last_contact_date_out
 *  - ac_last_contact_date_in
 *  - ac_contact_count
 *  - ac_contact_count_out
 *  - ac_contact_count_in
 */
class ActivityContactMigrationQuery extends ParametrizedMigrationQuery
{
    /** @var Schema */
    protected $schema;

    /** @var EntityMetadataHelper */
    protected $metadataHelper;

    /** @var ActivityContactProvider */
    protected $activityContactProvider;

    public function __construct(
        Schema $schema,
        EntityMetadataHelper $metadataHelper,
        ActivityContactProvider $activityContactProvider
    ) {
        $this->schema                  = $schema;
        $this->metadataHelper          = $metadataHelper;
        $this->activityContactProvider = $activityContactProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->addActivityContactColumns($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->addActivityContactColumns($logger, true);

        return $logger->getMessages();
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function addActivityContactColumns(LoggerInterface $logger, $dryRun = false)
    {
        $hasSchemaChanges          = false;
        $toSchema                  = clone $this->schema;
        $contactingActivityClasses = $this->activityContactProvider->getSupportedActivityClasses();

        $entities = $this->getConfigurableEntitiesData($logger);
        foreach ($entities as $entityClassName => $config) {
            // Skip excluded entity
            if (TargetExcludeList::isExcluded($entityClassName)) {
                continue;
            }

            if ($this->canAddField($config, $contactingActivityClasses)) {
                $tableName = $this->getTableName($config, $entityClassName);

                // Process only existing tables
                if (!$toSchema->hasTable($tableName)) {
                    continue;
                }

                $table        = $toSchema->getTable($tableName);
                $tableColumns = $table->getColumns();

                /**
                 * Check if entity already has all needed columns.
                 * If at least one is not present we should check and add it.
                 */
                if ($this->hasEntityNeededColumns($tableColumns)) {
                    continue;
                }

                foreach (ActivityScope::$fieldsConfiguration as $fieldName => $fieldConfig) {
                    if (!$table->hasColumn($fieldName)) {
                        $hasSchemaChanges = $this->addColumn($table, $fieldName, $fieldConfig);
                    }
                }
            }
        }

        $this->runSchemaRelated($logger, $dryRun, $hasSchemaChanges, $toSchema);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return array
     *  key - class name
     *  value - entity config array data
     */
    protected function getConfigurableEntitiesData(LoggerInterface $logger)
    {
        $result = [];

        $sql    = 'SELECT class_name, data FROM oro_entity_config WHERE mode = ?';
        $params = [ConfigModel::MODE_DEFAULT];
        $types  = [Types::STRING];

        $this->logQuery($logger, $sql, $params, $types);
        $rows = $this->connection->fetchAll($sql, $params, $types);
        foreach ($rows as $row) {
            $result[$row['class_name']] = $this->connection->convertToPHPValue($row['data'], Types::ARRAY);
        }

        return $result;
    }

    /**
     * @param Table $table
     * @param string $fieldName
     * @param array $fieldConfig
     * @return bool
     */
    protected function addColumn($table, $fieldName, $fieldConfig)
    {
        $hasSchemaChanges = true;
        $table->addColumn(
            $fieldName,
            $fieldConfig['type'],
            [
                'notnull' => false,
                OroOptions::KEY => array_merge(
                    [
                        ExtendOptionsManager::MODE_OPTION => $fieldConfig['mode']
                    ],
                    $fieldConfig['options']
                )
            ]
        );

        return $hasSchemaChanges;
    }

    /**
     * Run schema related SQLs manually because this query run when diff is already calculated by schema tool
     *
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param bool $hasSchemaChanges
     * @param Schema $toSchema
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function runSchemaRelated(LoggerInterface $logger, $dryRun, $hasSchemaChanges, $toSchema)
    {
        if ($hasSchemaChanges) {
            $comparator = new Comparator();
            $platform = $this->connection->getDatabasePlatform();
            $schemaDiff = $comparator->compare($this->schema, $toSchema);
            foreach ($schemaDiff->toSql($platform) as $query) {
                $this->logQuery($logger, $query);
                if (!$dryRun) {
                    $this->connection->query($query);
                }
            }
        }
    }

    /**
     * @param array $config
     * @param array $contactingActivityClasses
     * @return bool
     */
    protected function canAddField($config, $contactingActivityClasses)
    {
        return isset($config['extend']['is_extend'], $config['activity']['activities'])
        && $config['extend']['is_extend'] == true
        && $config['activity']['activities']
        && array_intersect($contactingActivityClasses, $config['activity']['activities']);
    }

    /**
     * @param array $tableColumns
     * @return bool
     */
    protected function hasEntityNeededColumns($tableColumns)
    {
        return false === (bool)array_diff(
            array_keys(ActivityScope::$fieldsConfiguration),
            array_intersect(array_keys($tableColumns), array_keys(ActivityScope::$fieldsConfiguration))
        );
    }

    /**
     * @param $config
     * @param $entityClassName
     *
     * @return string
     */
    protected function getTableName($config, $entityClassName)
    {
        if (isset($config['extend']['schema']['doctrine'][$entityClassName]['table'])) {
            $tableName = $config['extend']['schema']['doctrine'][$entityClassName]['table'];
        } else {
            $tableName = $this->metadataHelper->getTableNameByEntityClass($entityClassName);
        }
        return $tableName;
    }
}
