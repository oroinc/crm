<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateTaskStatusQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Update task status field from workflow step.'
        );
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
     * {@inheritdoc}
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof PostgreSqlPlatform) {
            $updateSql = "UPDATE orocrm_task AS t
                SET status_id = ts.id
                FROM oro_workflow_step AS ws, oro_enum_task_status AS ts
                WHERE t.workflow_step_id = ws.id AND ws.name = ts.id AND ws.workflow_name = 'task_flow'";

            $this->logQuery($logger, $updateSql);
            if (!$dryRun) {
                $this->connection->executeUpdate($updateSql);
            }
        }

        if ($platform instanceof MySqlPlatform) {
            $updateSql = "UPDATE orocrm_task AS t, oro_workflow_step AS ws, oro_enum_task_status AS ts
                SET t.status_id = ts.id
                WHERE t.workflow_step_id = ws.id AND ws.name = ts.id AND ws.workflow_name = 'task_flow'";

            $this->logQuery($logger, $updateSql);
            if (!$dryRun) {
                $this->connection->executeUpdate($updateSql);
            }
        }

        // set task status to open on tasks that had no assigned workflow steps
        $updateSql = "UPDATE orocrm_task SET status_id = 'open' WHERE status_id IS NULL";

        $this->logQuery($logger, $updateSql);
        if (!$dryRun) {
            $this->connection->executeUpdate($updateSql);
        }
    }
}
