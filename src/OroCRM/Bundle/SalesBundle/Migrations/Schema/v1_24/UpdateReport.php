<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class UpdateReport extends ParametrizedMigrationQuery implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new self());
    }

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
     * @param bool $dryRun
     * @param array $def
     * @param array $row
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateReport(LoggerInterface $logger, $dryRun, $def, $row)
    {
        $query = 'UPDATE oro_report SET definition = :definition WHERE id = :id';
        $this->executeQuery($logger, $dryRun, $def, $row, $query);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param array $def
     * @param array $row
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateSegment(LoggerInterface $logger, $dryRun, $def, $row)
    {
        $query = 'UPDATE oro_segment SET definition = :definition WHERE id = :id';
        $this->executeQuery($logger, $dryRun, $def, $row, $query);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    protected function migrateReport(LoggerInterface $logger, $dryRun)
    {
        $sql = 'SELECT r.id, r.definition, r.entity FROM oro_report r';

        $className = 'OroCRM\Bundle\SalesBundle\Entity\Lead';
        $oldField = 'status_label';
        $newField = 'status';
        $this->logQuery($logger, $sql);

        $rows = $this->connection->fetchAll($sql);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);
            $this->fixReportDefs($logger, $dryRun, $def, $row, $className, $oldField, $newField);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     */
    protected function migrateSegment(LoggerInterface $logger, $dryRun)
    {
        $sql = 'SELECT s.id, s.definition, s.entity FROM oro_segment s';

        $className = 'OroCRM\Bundle\SalesBundle\Entity\Lead';
        $oldField = 'status_label';
        $newField = 'status';
        $this->logQuery($logger, $sql);

        $rows = $this->connection->fetchAll($sql);
        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);
            $this->fixSegmentDefs($logger, $dryRun, $def, $row, $className, $oldField, $newField);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param array $def
     * @param array $row
     * @param string $className
     * @param string $oldField
     * @param string $newField
     */
    protected function fixSegmentDefs(LoggerInterface $logger, $dryRun, $def, $row, $className, $oldField, $newField)
    {
        if (isset($def['columns'])) {
            foreach ($def['columns'] as $key => $field) {
                if (isset($field['name']) && $row['entity'] === $className && $field['name'] === $oldField) {
                    $def['columns'][$key]['name'] = $newField;
                    $this->updateSegment($logger, $dryRun, $def, $row);
                }
            }
        }
        if (isset($def['filters'])) {
            foreach ($def['filters'] as $key => $field) {
                if (isset($field['columnName'])) {
                    $def = $this->processFilterDefinition($def, $row, $className, $oldField, $newField, $field, $key);
                    $def = $this->fixFilterCriterion($def, $field, $key);
                    $this->updateSegment($logger, $dryRun, $def, $row);
                }
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param array $def
     * @param array $row
     * @param string $className
     * @param string $oldField
     * @param string $newField
     */
    protected function fixReportDefs(LoggerInterface $logger, $dryRun, $def, $row, $className, $oldField, $newField)
    {
        if (isset($def['columns'])) {
            foreach ($def['columns'] as $key => $field) {
                if (isset($field['name'])) {
                    if ($row['entity'] === $className && $field['name'] === $oldField) {
                        $def['columns'][$key]['name'] = $newField;
                    } else {
                        $def['columns'][$key]['name']
                            = str_replace('Lead::status_label', 'Lead::status', $field['name']);
                    }
                    $this->updateReport($logger, $dryRun, $def, $row);
                }
            }
        }
        if (isset($def['filters'])) {
            foreach ($def['filters'] as $key => $field) {
                if (isset($field['columnName'])) {
                    $def = $this->processFilterDefinition($def, $row, $className, $oldField, $newField, $field, $key);
                    $def = $this->fixFilterCriterion($def, $field, $key);
                    $this->updateReport($logger, $dryRun, $def, $row);
                }
            }
        }
        if (isset($def['grouping_columns'])) {
            foreach ($def['grouping_columns'] as $key => $field) {
                if (isset($field['name'])) {
                    if ($field['name'] === $oldField) {
                        $def['grouping_columns'][$key]['name'] = $newField;
                    } else {
                        $def['grouping_columns'][$key]['name']
                            = str_replace('Lead::status_label', 'Lead::status', $field['name']);
                    }
                    $this->updateReport($logger, $dryRun, $def, $row);
                }
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param array $def
     * @param array $row
     * @param string $query
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeQuery(LoggerInterface $logger, $dryRun, $def, $row, $query)
    {
        $params = ['definition' => json_encode($def), 'id' => $row['id']];
        $types = ['definition' => 'text', 'id' => 'integer'];
        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, $params, $types);
        }
    }

    /**
     * @param array $def
     * @param array $row
     * @param string $className
     * @param string $oldField
     * @param string $newField
     * @param array $field
     * @param string $key
     * @return mixed
     */
    protected function processFilterDefinition($def, $row, $className, $oldField, $newField, $field, $key)
    {
        if ($row['entity'] === $className && $field['columnName'] === $oldField) {
            $def['filters'][$key]['columnName'] = $newField;
        } else {
            $def['filters'][$key]['columnName']
                = str_replace('Lead::status_label', 'Lead::status', $field['columnName']);
        }

        return $def;
    }

    /**
     * @param array $def
     * @param array $field
     * @param string $key
     * @return array
     */
    protected function fixFilterCriterion($def, $field, $key)
    {
        $paramOldClassName = 'OroCRM\Bundle\SalesBundle\Entity\LeadStatus';
        $paramNewClassName = ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE);
        if (isset($field['criterion']['data']['params']['class'])
            && $field['criterion']['data']['params']['class'] === $paramOldClassName
            && $field['criterion']['filter'] === 'dictionary'
        ) {
            $def['filters'][$key]['criterion']['data']['params']['class'] = $paramNewClassName;
            $def['filters'][$key]['criterion']['filter'] = 'enum';
        }

        return $def;
    }
}
