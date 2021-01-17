<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_8;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateCampaignPerformanceReportQuery extends ParametrizedMigrationQuery
{
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
    public function execute(LoggerInterface $logger)
    {
        $this->doExecute($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $sql    = 'SELECT r.id, r.definition'
            . ' FROM oro_report r'
            . ' WHERE r.name = :name';
        $params = ['name' => 'Campaign Performance'];
        $types  = ['name' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);

        $rows = $this->connection->fetchAll($sql, $params, $types);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);
            if (isset($def['grouping_columns'])
                && count($def['grouping_columns']) === 1
                && isset($def['grouping_columns'][0]['name'])
                && $def['grouping_columns'][0]['name'] === 'id'
            ) {
                $def['grouping_columns'] = [
                    ['name' => 'code'],
                    ['name' => 'name'],
                ];

                $query  = 'UPDATE oro_report SET definition = :definition WHERE id = :id';
                $params = ['definition' => json_encode($def), 'id' => $row['id']];
                $types  = ['definition' => 'text', 'id' => 'integer'];
                $this->logQuery($logger, $query, $params, $types);
                if (!$dryRun) {
                    $this->connection->executeStatement($query, $params, $types);
                }
            }
        }
    }
}
