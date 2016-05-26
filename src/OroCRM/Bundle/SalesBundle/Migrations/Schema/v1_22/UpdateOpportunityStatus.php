<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateOpportunityStatus implements
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
            'in_progress',
            'won',
            'lost'
        ];
        $connection = $this->container->get('doctrine')->getConnection();
        $oldStatuses = $connection->fetchAll('SELECT name, label FROM orocrm_sales_opport_status');
        $newStatuses = $connection->fetchAll('SELECT id, priority FROM oro_enum_opportunity_status');
        $oldStatuses = $this->buildOneDimensionArray($oldStatuses, 'name', 'label');
        $newStatuses = $this->buildOneDimensionArray($newStatuses, 'id', 'priority');
        $oldStatusesToAdd = array_diff_key($oldStatuses, $newStatuses);

        if (is_array($oldStatusesToAdd) && count($oldStatusesToAdd)) {
            $defaultStatuses = array_merge($defaultStatuses, array_keys($oldStatusesToAdd));
        }

        $priority = 1;
        if (is_array($newStatuses) && count($newStatuses)) {
            $priority = max($newStatuses) + 1;
        }

        $this->updateOpportunityStatusTable($queries, $oldStatusesToAdd, $priority);
        $this->updateOpportunityTable($queries, $defaultStatuses);
    }

    /**
     * @param QueryBag $queries
     * @param array $statuses
     * @param int $priority
     */
    protected function updateOpportunityStatusTable($queries, $statuses, $priority)
    {
        foreach ($statuses as $id => $name) {
            $query  = 'INSERT INTO oro_enum_opportunity_status (id, name, priority, is_default)
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
    protected function updateOpportunityTable($queries, $statuses)
    {
        $query = 'UPDATE orocrm_sales_opportunity SET status_id = :status_id WHERE status_name = :status_name';
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
