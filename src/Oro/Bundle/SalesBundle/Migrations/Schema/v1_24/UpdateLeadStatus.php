<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Query\OutdatedEnumDataValue;
use Oro\Bundle\EntityExtendBundle\Migration\Query\OutdatedInsertEnumValuesQuery;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Creates Lead absent statuses and updates Lead statuses.
 */
class UpdateLeadStatus implements
    Migration,
    ConnectionAwareInterface,
    OutdatedExtendExtensionAwareInterface,
    OrderedMigrationInterface
{
    use ConnectionAwareTrait;
    use OutdatedExtendExtensionAwareTrait;

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
        $defaultStatuses = ['new', 'qualified', 'canceled'];
        $oldStatuses = $this->connection->fetchAllAssociative('SELECT name, label FROM orocrm_sales_lead_status');
        $newStatuses = $this->connection->fetchAllAssociative(sprintf(
            'SELECT id, priority FROM %s',
            $this->outdatedExtendExtension::generateEnumTableName('lead_status')
        ));
        $oldStatuses = $this->buildOneDimensionArray($oldStatuses, 'name', 'label');
        $newStatuses = $this->buildOneDimensionArray($newStatuses, 'id', 'priority');
        $oldStatusesToAdd = array_diff_key($oldStatuses, $newStatuses);
        if ($oldStatusesToAdd) {
            $defaultStatuses = array_merge($defaultStatuses, array_keys($oldStatusesToAdd));
        }

        $this->updateLeadStatusTable($queries, $oldStatusesToAdd, $newStatuses ? max($newStatuses) + 1 : 1);
        $this->updateLeadTable($queries, $defaultStatuses);
    }

    private function updateLeadStatusTable(QueryBag $queries, array $statuses, int $priority): void
    {
        $values = [];
        foreach ($statuses as $id => $name) {
            $values[] = new OutdatedEnumDataValue($id, $name, $priority);
            $priority++;
        }
        $queries->addPostQuery(
            new OutdatedInsertEnumValuesQuery($this->outdatedExtendExtension, 'lead_status', $values)
        );
    }

    private function updateLeadTable(QueryBag $queries, array $statuses): void
    {
        $query = 'UPDATE orocrm_sales_lead SET status_id = :status_id WHERE status_name = :status_name';
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
