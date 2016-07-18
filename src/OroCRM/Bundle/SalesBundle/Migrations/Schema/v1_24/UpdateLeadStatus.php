<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateLeadStatus implements
    Migration,
    ContainerAwareInterface,
    OrderedMigrationInterface
{
    /** @var ContainerInterface */
    protected $container;

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
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $defaultStatuses = [
            'new',
            'qualified',
            'canceled'
        ];
        $connection = $this->container->get('doctrine')->getConnection();
        $oldStatuses = $connection->fetchAll('SELECT name, label FROM orocrm_sales_lead_status');
        $newStatuses = $connection->fetchAll('SELECT id, priority FROM oro_enum_lead_status');
        $oldStatuses = $this->buildOneDimensionArray($oldStatuses, 'name', 'label');
        $newStatuses = $this->buildOneDimensionArray($newStatuses, 'id', 'priority');
        $oldStatusesToAdd = array_diff_key($oldStatuses, $newStatuses);

        if (count($oldStatusesToAdd) > 0) {
            $defaultStatuses = array_merge($defaultStatuses, array_keys($oldStatusesToAdd));
        }

        $priority = 1;
        if (is_array($newStatuses) && count($newStatuses)) {
            $priority = max($newStatuses) + 1;
        }

        $this->updateLeadStatusTable($queries, $oldStatusesToAdd, $priority);
        $this->updateLeadTable($queries, $defaultStatuses);
    }

    /**
     * @param QueryBag $queries
     * @param array $statuses
     * @param int $priority
     */
    protected function updateLeadStatusTable($queries, $statuses, $priority)
    {
        foreach ($statuses as $id => $name) {
            $query  = 'INSERT INTO oro_enum_lead_status (id, name, priority, is_default)
                       VALUES (:id, :name, :priority, 0)';
            $params = ['id' => $id, 'name' => $name, 'priority' => $priority];
            $types  = ['id' => Type::STRING, 'name' => Type::STRING, 'priority' => Type::INTEGER];

            $migrationQuery = new ParametrizedSqlMigrationQuery();
            $migrationQuery->addSql(
                $query,
                $params,
                $types
            );
            $queries->addPostQuery($migrationQuery);

            $priority++;
        }
    }

    /**
     * @param QueryBag $queries
     * @param array $statuses
     */
    protected function updateLeadTable($queries, $statuses)
    {
        $query = 'UPDATE orocrm_sales_lead SET status_id = :status_id WHERE status_name = :status_name';
        foreach ($statuses as $status) {
            $migrationQuery = new ParametrizedSqlMigrationQuery();
            $migrationQuery->addSql(
                $query,
                ['status_id' => $status, 'status_name' => $status],
                ['status_id' => Type::STRING, 'status_name' => Type::STRING]
            );
            $queries->addPostQuery($migrationQuery);
        }
    }

    /**
     * @param array $rows
     * @param string $key
     * @param string $value
     *
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
