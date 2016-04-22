<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class InsertTaskStatusesQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Insert default task statuses.'
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
     * @param bool $dryRun
     */
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $sql = 'INSERT INTO oro_enum_task_status (id, name, priority, is_default) 
            VALUES (:id, :name, :priority, :is_default)';

        $statuses = [
            [
                ':id' => 'open',
                ':name' => 'Open',
                ':priority' => 1,
                ':is_default' => true,
            ],
            [
                ':id' => 'in_progress',
                ':name' => 'In Progress',
                ':priority' => 2,
                ':is_default' => false,
            ],
            [
                ':id' => 'closed',
                ':name' => 'Closed',
                ':priority' => 3,
                ':is_default' => false,
            ],
        ];

        $types = [
            'id' => 'string',
            'name' => 'string',
            'priority' => 'integer',
            'is_default' => 'boolean'
        ];

        foreach ($statuses as $status) {
            $this->logQuery($logger, $sql, $status, $types);
            if (!$dryRun) {
                $this->connection->executeUpdate($sql, $status, $types);
            }
        }
    }
}
