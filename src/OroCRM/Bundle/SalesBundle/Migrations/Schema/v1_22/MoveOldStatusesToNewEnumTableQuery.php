<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class MoveOldStatusesToNewEnumTableQuery extends ParametrizedMigrationQuery
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
        $this->insertOldStatuses($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function insertOldStatuses(LoggerInterface $logger, $dryRun = false)
    {
        $oldStatuses = $this->connection->fetchAll(
            'SELECT name, label FROM orocrm_sales_opport_status'
        );

        $newStatuses = $this->connection->fetchAll(
            'SELECT id, priority FROM oro_enum_opportunity_status'
        );

        $oldStatuses = $this->buildOneDimensionArray($oldStatuses, 'name', 'label');
        $newStatuses = $this->buildOneDimensionArray($newStatuses, 'id', 'priority');
        $statusesToAdd = array_diff_key($oldStatuses, $newStatuses);

        $priority = 1;
        if (is_array($newStatuses) && count($newStatuses)) {
            $priority = max($newStatuses) + 1;
        }

        foreach ($statusesToAdd as $id => $name) {
            $query  = 'INSERT INTO oro_enum_opportunity_status (id, name, priority, is_default)
                       VALUES (:id, :name, :priority, 0)';
            $params = ['id' => $id, 'name' => $name, 'priority' => $priority];
            $types  = ['id' => 'string', 'name' => 'string', 'priority' => 'integer'];
            $this->logQuery($logger, $query, $params, $types);
            if (!$dryRun) {
                $this->connection->executeQuery($query, $params, $types);
            }
            $priority++;
        }
    }

    /**
     * @param array $rows
     * @param string $key
     * @param string $value
     * @return array
     */
    protected function buildOneDimensionArray($rows, $key, $value)
    {
        $result = [];

        foreach ($rows as $row) {
            $result[$row[$key]] = $row[$value];
        }

        return $result;
    }
}
