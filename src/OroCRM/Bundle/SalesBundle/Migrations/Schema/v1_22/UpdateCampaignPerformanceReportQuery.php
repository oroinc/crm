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
        $fixFields = [
            [4, 'Number Won'],
            [5, 'Number Lost']
        ];

        $sql = 'SELECT r.id, r.definition'
            . ' FROM oro_report r'
            . ' WHERE r.name = :name';
        $params = ['name' => 'Campaign Performance'];
        $types  = ['name' => 'string'];
        $this->logQuery($logger, $sql, $params, $types);

        $rows = $this->connection->fetchAll($sql, $params, $types);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);

            foreach ($fixFields as $number => $checkLabel) {
                if (isset($def['columns'][$number])) {
                    $field = $def['columns'][$number];
                    if ($field['label'] === $checkLabel && isset($field['label'], $field['name'])) {
                        $def = $this->fixDef($field, $number, $def);
                        $this->executeQuery($logger, $dryRun, $def, $row);
                    }
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

    /**
     * @param $field
     * @param $number
     * @param $def
     * @return mixed
     */
    protected function fixDef($field, $number, $def)
    {
        $def['columns'][$number]['name']
            = str_replace('Opportunity::status_label', 'Opportunity::status', $field['name']);

        return $def;
    }
}
