<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_30;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Psr\Log\LoggerInterface;

class UpdateReport extends ParametrizedMigrationQuery implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
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
        //Migrate Reports
        $this->migrate(
            $logger,
            $dryRun,
            'SELECT r.id, r.definition, r.entity FROM oro_report r',
            'UPDATE oro_report SET definition = :definition WHERE id = :id'
        );

        //Migrate Segments
        $this->migrate(
            $logger,
            $dryRun,
            'SELECT s.id, s.definition, s.entity FROM oro_segment s',
            'UPDATE oro_segment SET definition = :definition WHERE id = :id'
        );
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param string $fetchQuery
     * @param string $updateQuery
     */
    protected function migrate(LoggerInterface $logger, $dryRun, $fetchQuery, $updateQuery)
    {
        $className = 'Oro\Bundle\SalesBundle\Entity\Opportunity';
        $replacements = [
            'closeRevenue'      => 'closeRevenueBaseCurrency',
            'closeRevenueValue' => 'closeRevenueBaseCurrency',
            'budgetAmount'      => 'budgetAmountBaseCurrency',
            'budgetAmountValue' => 'budgetAmountBaseCurrency',
        ];

        $this->logQuery($logger, $fetchQuery);
        $rows = $this->connection->fetchAll($fetchQuery);

        foreach ($rows as $row) {
            $def = json_decode($row['definition'], true);
            $fixedDef = $this->fixDefinition($def, $row, $className, $replacements);
            if ($fixedDef !== $def) {
                $this->executeQuery($logger, $dryRun, $fixedDef, $row, $updateQuery);
            }
        }
    }

    /**
     * @param string $fieldDefinition
     * @return string
     */
    private function replaceFieldDefinition($fieldDefinition)
    {
        return str_replace(
            [
                'Opportunity::closeRevenue',
                'Opportunity::closeRevenueValue',
                'Opportunity::budgetAmount',
                'Opportunity::budgetAmountValue',
            ],
            [
                'Opportunity::closeRevenueBaseCurrency',
                'Opportunity::closeRevenueBaseCurrency',
                'Opportunity::budgetAmountBaseCurrency',
                'Opportunity::budgetAmountBaseCurrency',
            ],
            $fieldDefinition
        );
    }

    /**
     * @param array  $def
     * @param array  $row
     * @param string $className
     * @param array  $replacements
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function fixDefinition($def, $row, $className, $replacements)
    {
        if (isset($def['columns'])) {
            foreach ($def['columns'] as $key => $field) {
                if (isset($field['name'])) {
                    $def = $this->proseccColumnDefinition($def, $row, $className, $replacements, $field, $key);
                }
            }
        }
        if (isset($def['filters'])) {
            foreach ($def['filters'] as $key => $field) {
                if (isset($field['columnName'])) {
                    $def = $this->processFilterDefinition($def, $row, $className, $replacements, $field, $key);
                }
            }
        }
        if (isset($def['grouping_columns'])) {
            foreach ($def['grouping_columns'] as $key => $field) {
                if (isset($field['name'])) {
                    $def = $this->processGroupDefinition($def, $replacements, $field, $key);
                }
            }
        }

        return $def;
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
            $this->connection->executeStatement($query, $params, $types);
        }
    }

    /**
     * @param array $def
     * @param array $row
     * @param string $className
     * @param array $replacements
     * @param array $field
     * @param string $key
     * @return array
     */
    protected function processFilterDefinition($def, $row, $className, array $replacements, $field, $key)
    {
        if ($row['entity'] === $className && isset($replacements[$field['columnName']])) {
            $def['filters'][$key]['columnName'] = $replacements[$field['columnName']];
        } else {
            $def['filters'][$key]['columnName'] = $this->replaceFieldDefinition($field['columnName']);
        }

        return $def;
    }

    /**
     * @param $def
     * @param $row
     * @param $className
     * @param array $replacements
     * @param $field
     * @param $key
     * @return array
     */
    protected function proseccColumnDefinition($def, $row, $className, array $replacements, $field, $key)
    {
        if ($row['entity'] === $className && isset($replacements[$field['name']])) {
            $def['columns'][$key]['name'] = $replacements[$field['name']];
        } else {
            $def['columns'][$key]['name'] = $this->replaceFieldDefinition($field['name']);
        }

        return $def;
    }

    /**
     * @param $def
     * @param array $replacements
     * @param $field
     * @param $key
     * @return array
     */
    protected function processGroupDefinition($def, array $replacements, $field, $key)
    {
        if (isset($replacements[$field['name']])) {
            $def['grouping_columns'][$key]['name'] = $replacements[$field['name']];
        } else {
            $def['grouping_columns'][$key]['name'] = $this->replaceFieldDefinition($field['name']);
        }

        return $def;
    }
}
