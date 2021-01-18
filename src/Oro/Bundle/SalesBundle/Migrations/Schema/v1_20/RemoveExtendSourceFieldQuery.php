<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_20;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class RemoveExtendSourceFieldQuery extends ParametrizedMigrationQuery
{
    /**
     * {inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $this->doExecute($logger, true);

        return $logger->getMessages();
    }

    /**
     * {inheritdoc}
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
        $this->removeFieldIndexes($logger, $dryRun);
        $this->removeField($logger, $dryRun);
        $this->cleanUpEntity($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function removeFieldIndexes(LoggerInterface $logger, $dryRun = false)
    {
        $sql = 'DELETE FROM oro_entity_config_index_value'
            . ' WHERE entity_id IS NULL AND field_id IN ('
            . ' SELECT oecf.id FROM oro_entity_config_field AS oecf'
            . ' WHERE oecf.field_name = :field'
            . ' AND oecf.entity_id IN ('
            . ' SELECT oec.id'
            . ' FROM oro_entity_config AS oec'
            . ' WHERE oec.class_name = :class'
            . ' ))';

        $params = ['class' => 'Oro\Bundle\SalesBundle\Entity\Lead', 'field' => 'extend_source'];
        $this->logQuery($logger, $sql, $params);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function removeField(LoggerInterface $logger, $dryRun = false)
    {
        $sql = 'DELETE FROM oro_entity_config_field'
            . ' WHERE field_name = :field'
            . ' AND entity_id IN ('
            . ' SELECT id'
            . ' FROM oro_entity_config'
            . ' WHERE class_name = :class'
            . ' )';

        $params = ['class' => 'Oro\Bundle\SalesBundle\Entity\Lead', 'field' => 'extend_source'];
        $this->logQuery($logger, $sql, $params);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function cleanUpEntity(LoggerInterface $logger, $dryRun = false)
    {
        $rows = $this->connection->fetchAll(
            'SELECT id, data FROM oro_entity_config WHERE class_name = :class',
            ['class' => 'Oro\Bundle\SalesBundle\Entity\Lead']
        );
        foreach ($rows as $row) {
            $data = $this->connection->convertToPHPValue($row['data'], 'array');
            unset($data['extend']['schema']['relation']['extend_source']);
            unset($data['extend']['index']['extend_source']);

            $query  = 'UPDATE oro_entity_config SET data = :data WHERE id = :id';
            $params = ['data' => $data, 'id' => $row['id']];
            $types  = ['data' => 'array', 'id' => 'integer'];
            $this->logQuery($logger, $query, $params, $types);
            if (!$dryRun) {
                $this->connection->executeStatement($query, $params, $types);
            }
        }
    }
}
