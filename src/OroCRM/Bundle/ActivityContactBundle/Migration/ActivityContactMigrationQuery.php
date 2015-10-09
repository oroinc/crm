<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migration;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Comparator;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\EntityMetadataHelper;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class ActivityContactMigrationQuery extends ParametrizedMigrationQuery
{
    /** @var Schema */
    protected $schema;

    /** @var EntityMetadataHelper */
    protected $metadataHelper;

    /** @var ActivityContactProvider */
    protected $activityContactProvider;

    /**
     * @param Schema                  $schema
     * @param EntityMetadataHelper    $metadataHelper
     * @param ActivityContactProvider $activityContactProvider
     */
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
            if (isset($config['extend']['is_extend'], $config['activity']['activities'])
                && $config['extend']['is_extend'] == true
                && $config['activity']['activities']
                && array_intersect($contactingActivityClasses, $config['activity']['activities'])
            ) {
                if (isset($config['extend']['schema']['doctrine'][$entityClassName]['table'])) {
                    $tableName = $config['extend']['schema']['doctrine'][$entityClassName]['table'];
                } else {
                    $tableName = $this->metadataHelper->getTableNameByEntityClass($entityClassName);
                }

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
                if (false === (bool) array_diff(
                    array_keys(ActivityScope::$fieldsConfiguration),
                    array_intersect(array_keys($tableColumns), array_keys(ActivityScope::$fieldsConfiguration))
                )) {
                    continue;
                }

                foreach (ActivityScope::$fieldsConfiguration as $fieldName => $fieldConfig) {
                    if (!$table->hasColumn($fieldName)) {
                        $hasSchemaChanges = true;
                        $table->addColumn(
                            $fieldName,
                            $fieldConfig['type'],
                            [
                                'notnull'       => false,
                                OroOptions::KEY => array_merge(
                                    [
                                        ExtendOptionsManager::MODE_OPTION => $fieldConfig['mode']
                                    ],
                                    $fieldConfig['options']
                                )
                            ]
                        );
                    }
                }
            }
        }

        if ($hasSchemaChanges) {
            // Run schema related SQLs manually because this query run when diff is already calculated by schema tool
            $comparator = new Comparator();
            $platform   = $this->connection->getDatabasePlatform();
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
        $types  = [Type::STRING];

        $this->logQuery($logger, $sql, $params, $types);
        $rows = $this->connection->fetchAll($sql, $params, $types);
        foreach ($rows as $row) {
            $result[$row['class_name']] = $this->connection->convertToPHPValue($row['data'], Type::TARRAY);
        }

        return $result;
    }
}
