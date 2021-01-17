<?php

namespace Oro\Bundle\ActivityContactBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Connection;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Sets available permissions to VIEW for ActivityContact fields (ac_last_contact_date, ac_last_contact_date_out, ...).
 */
class UpdateContactFieldsConfigQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->migrateConfigs($logger, true);

        return $logger->getMessages();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $this->migrateConfigs($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function migrateConfigs(LoggerInterface $logger, $dryRun = false)
    {
        $fieldsConfigs = $this->getFieldConfigs($logger);
        $query  = 'UPDATE oro_entity_config_field SET data = :data WHERE id = :id';
        $types  = ['data' => 'array', 'id' => 'integer'];
        foreach ($fieldsConfigs as $id => $data) {
            if (!array_key_exists('security', $data)) {
                $data['security'] = [];
            }
            if (!array_key_exists('permissions', $data['security'])) {
                $data['security']['permissions'] = 'VIEW';
            }

            $params = ['data' => $data, 'id' => $id];
            $this->logQuery($logger, $query, $params, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($query, $params, $types);
            }
        }
    }

    /**
     * Returns array with ContactActivity fields configurations
     *
     * @param LoggerInterface $logger
     *
     * @return array
     *   key - field id
     *   value - field configuration
     */
    protected function getFieldConfigs(LoggerInterface $logger)
    {
        $fieldNames = array_keys(ActivityScope::$fieldsConfiguration);
        $params = ['fields' => $fieldNames];
        $types = ['fields' => Connection::PARAM_STR_ARRAY];
        $sql = 'SELECT id, data FROM oro_entity_config_field WHERE field_name in (:fields)';
        $this->logQuery($logger, $sql, $params, $types);

        $rows = $this->connection->fetchAll($sql, $params, $types);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['id']] = $this->connection->convertToPHPValue($row['data'], 'array');
        }

        return $result;
    }
}
