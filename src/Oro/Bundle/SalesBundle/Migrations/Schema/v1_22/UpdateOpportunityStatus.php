<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Query\EnumDataValue;
use Oro\Bundle\EntityExtendBundle\Migration\Query\InsertEnumValuesQuery;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateOpportunityStatus implements
    Migration,
    ConnectionAwareInterface,
    ExtendExtensionAwareInterface,
    OrderedMigrationInterface
{
    use ConnectionAwareTrait;
    use ExtendExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getOrder(): int
    {
        return 2;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $defaultStatuses = ['in_progress', 'won', 'lost'];
        $oldStatuses = $this->connection->fetchAll('SELECT name, label FROM orocrm_sales_opport_status');
        $newStatuses = $this->connection->fetchAll(sprintf(
            'SELECT id, priority FROM %s',
            $this->extendExtension->getNameGenerator()->generateEnumTableName('opportunity_status')
        ));
        $oldStatuses = $this->buildOneDimensionArray($oldStatuses, 'name', 'label');
        $newStatuses = $this->buildOneDimensionArray($newStatuses, 'id', 'priority');
        $oldStatusesToAdd = array_diff_key($oldStatuses, $newStatuses);
        if ($oldStatusesToAdd) {
            $defaultStatuses = array_merge($defaultStatuses, array_keys($oldStatusesToAdd));
        }

        $this->updateOpportunityStatusTable($queries, $oldStatusesToAdd, $newStatuses ? max($newStatuses) + 1 : 1);
        $this->updateOpportunityTable($queries, $defaultStatuses);
    }

    private function updateOpportunityStatusTable(QueryBag $queries, array $statuses, int $priority): void
    {
        $values = [];
        foreach ($statuses as $id => $name) {
            $values[] = new EnumDataValue($id, $name, $priority);
            $priority++;
        }
        $queries->addPostQuery(new InsertEnumValuesQuery($this->extendExtension, 'opportunity_status', $values));
    }

    private function updateOpportunityTable(QueryBag $queries, array $statuses): void
    {
        $query = 'UPDATE orocrm_sales_opportunity SET status_id = :status_id WHERE status_name = :status_name';
        foreach ($statuses as $status) {
            $migrationQuery = new ParametrizedSqlMigrationQuery();
            $migrationQuery->addSql(
                $query,
                ['status_id' => $status, 'status_name' => $status],
                ['status_id' => Types::STRING, 'status_name' => Types::STRING]
            );
            $queries->addPostQuery($migrationQuery);
        }
    }

    private function buildOneDimensionArray(array $rows, string $key, string $value): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[$row[$key]] = $row[$value];
        }

        return $result;
    }
}
