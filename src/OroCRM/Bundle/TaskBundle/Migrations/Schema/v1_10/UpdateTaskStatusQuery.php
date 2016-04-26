<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

class UpdateTaskStatusQuery extends ParametrizedMigrationQuery
{
    /** @var $extendExtension */
    protected $extendExtension;

    /**
     * @param ExtendExtension $extendExtension
     */
    public function __construct(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

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
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $platform = $this->connection->getDatabasePlatform();
        if ($platform instanceof PostgreSqlPlatform) {
            $this->updatePostgres($logger, $dryRun);
        }

        if ($platform instanceof MySqlPlatform) {
            $this->updateMysql($logger, $dryRun);
        }

        $this->updateDefaultTaskStatus($logger, $dryRun, 'open');
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function updatePostgres(LoggerInterface $logger, $dryRun)
    {
        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('task_status');

        $sql = 'UPDATE orocrm_task AS t' .
               ' SET status_id = ts.id' .
               ' FROM oro_workflow_step AS ws, %s AS ts' .
               ' WHERE t.workflow_step_id = ws.id AND ws.name = ts.id AND ws.workflow_name = :workflow_name';
        $sql = sprintf($sql, $tableName);

        $params = ['workflow_name' => 'task_flow'];
        $types  = ['workflow_name' => 'string'];

        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function updateMysql(LoggerInterface $logger, $dryRun)
    {
        $tableName = $this->extendExtension->getNameGenerator()->generateEnumTableName('task_status');

        $sql    = 'UPDATE orocrm_task AS t, oro_workflow_step AS ws, %s AS ts' .
                  ' SET t.status_id = ts.id' .
                  ' WHERE t.workflow_step_id = ws.id AND ws.name = ts.id AND ws.workflow_name = :workflow_name';
        $sql = sprintf($sql, $tableName);

        $params = ['workflow_name' => 'task_flow'];
        $types  = ['workflow_name' => 'string'];

        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }
    }

    /**
     * Set task status to open on tasks that had no assigned workflow steps
     *
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     * @param string          $defaultStatus
     */
    protected function updateDefaultTaskStatus(LoggerInterface $logger, $dryRun, $defaultStatus)
    {
        $sql    = 'UPDATE orocrm_task SET status_id = :status_id WHERE status_id IS NULL';
        $params = ['status_id' => $defaultStatus];
        $types  = ['status_id' => 'string'];

        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($sql, $params, $types);
        }
    }
}
