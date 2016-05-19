<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

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
        $sql = 'SELECT r.id, r.definition'
            . ' FROM oro_report r'
            . ' WHERE r.name = :name';
        $params = ['name' => 'Campaign Performance'];
        $types  = ['name' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);

        $rows = $this->connection->fetchAll($sql, $params, $types);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);

            if (isset($def['columns'][4])) {
                $field = $def['columns'][4];
                if ($field['label'] === 'Number Won' && isset($field['label'], $field['name'])) {
                    $def['columns'][4]['name']
                        = str_replace('Opportunity::status_label', 'Opportunity::status', $field['name']);
                    $this->executeQuery($logger, $dryRun, $def, $row);
                }
            }

            if (isset($def['columns'][5])) {
                $field = $def['columns'][5];
                if ($field['label'] === 'Number Lost' && isset($field['label'], $field['name'])) {
                    $def['columns'][5]['name']
                        = str_replace('Opportunity::status_label', 'Opportunity::status', $field['name']);
                    $this->executeQuery($logger, $dryRun, $def, $row);
                }
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param $dryRun
     * @param $def
     * @param $row
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeQuery(LoggerInterface $logger, $dryRun, $def, $row)
    {
        $query = 'UPDATE oro_report SET definition = :definition WHERE id = :id';
        $params = ['definition' => json_encode($def), 'id' => $row['id']];
        $types = ['definition' => 'text', 'id' => 'integer'];
        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, $params, $types);
        }
    }
}
