<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class ActivateAllChannelsQuery extends ParametrizedMigrationQuery
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
        $sql = 'UPDATE orocrm_channel SET status = :status';
        $params = ['status' => true];
        $types = ['status' => Types::BOOLEAN];

        $this->logQuery($logger, $sql, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($sql, $params, $types);
        }
    }
}
