<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateReportQuery extends ParametrizedMigrationQuery
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
        $this->migrateReport($logger, $dryRun);
        $this->migrateSegment($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param $dryRun
     * @param $def
     * @param $row
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateReport(LoggerInterface $logger, $dryRun, $def, $row)
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
     * @param LoggerInterface $logger
     * @param $dryRun
     * @param $def
     * @param $row
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateSegment(LoggerInterface $logger, $dryRun, $def, $row)
    {
        $query = 'UPDATE oro_segment SET definition = :definition WHERE id = :id';
        $params = ['definition' => json_encode($def), 'id' => $row['id']];
        $types = ['definition' => 'text', 'id' => 'integer'];
        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param $dryRun
     */
    protected function migrateReport(LoggerInterface $logger, $dryRun)
    {
        $className = 'OroCRM\Bundle\SalesBundle\Entity\Opportunity';
        $oldField = 'status_label';
        $newField = 'status';

        $sql = 'SELECT r.id, r.definition, r.entity FROM oro_report r';
        $this->logQuery($logger, $sql);

        $rows = $this->connection->fetchAll($sql);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);

            $this->fixReportDef($logger, $dryRun, $def, $row, $className, $oldField, $newField);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param $dryRun
     */
    protected function migrateSegment(LoggerInterface $logger, $dryRun)
    {
        $className = 'OroCRM\Bundle\SalesBundle\Entity\Opportunity';
        $oldField = 'status_label';
        $newField = 'status';

        $sql = 'SELECT s.id, s.definition, s.entity FROM oro_segment s';
        $this->logQuery($logger, $sql);

        $rows = $this->connection->fetchAll($sql);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);

            $this->fixSegmentDef($logger, $dryRun, $def, $row, $className, $oldField, $newField);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param $dryRun
     * @param $def
     * @param $row
     * @param $className
     * @param $oldField
     * @param $newField
     */
    protected function fixSegmentDef(LoggerInterface $logger, $dryRun, $def, $row, $className, $oldField, $newField)
    {
        if (isset($def['columns'])) {
            foreach ($def['columns'] as $key => $field) {
                if (isset($field['name'])
                    && $row['entity'] === $className
                    && $field['name'] === $oldField
                ) {
                    $def['columns'][$key]['name'] = $newField;
                    $this->updateSegment($logger, $dryRun, $def, $row);
                }
            }
        }
        if (isset($def['filters'])) {
            foreach ($def['filters'] as $key => $field) {
                if (isset($field['columnName'])) {
                    if (
                        $row['entity'] === $className
                        && $field['columnName'] === $oldField
                    ) {
                        $def['filters'][$key]['columnName'] = $newField;
                    } else {
                        $def['filters'][$key]['columnName']
                            = str_replace('Opportunity::status_label', 'Opportunity::status', $field['columnName']);
                    }
                    $this->updateSegment($logger, $dryRun, $def, $row);
                }
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param $dryRun
     * @param $def
     * @param $row
     * @param $className
     * @param $oldField
     * @param $newField
     */
    protected function fixReportDef(LoggerInterface $logger, $dryRun, $def, $row, $className, $oldField, $newField)
    {
        if (isset($def['columns'])) {
            foreach ($def['columns'] as $key => $field) {
                if (isset($field['name'])) {
                    if (
                        $row['entity'] === $className
                        && $field['name'] === $oldField
                    ) {
                        $def['columns'][$key]['name'] = $newField;
                    } else {
                        $def['columns'][$key]['name']
                            = str_replace('Opportunity::status_label', 'Opportunity::status', $field['name']);
                    }
                    $this->updateReport($logger, $dryRun, $def, $row);
                }
            }
        }
        if (isset($def['filters'])) {
            foreach ($def['filters'] as $key => $field) {
                if (isset($field['columnName'])) {
                    if (
                        $row['entity'] === $className
                        && $field['columnName'] === $oldField
                    ) {
                        $def['filters'][$key]['columnName'] = $newField;
                    } else {
                        $def['filters'][$key]['columnName']
                            = str_replace('Opportunity::status_label', 'Opportunity::status', $field['columnName']);
                    }
                    $this->updateReport($logger, $dryRun, $def, $row);
                }
            }
        }
    }
}
