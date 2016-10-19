<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Platforms\PostgreSQL92Platform;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateChannelJsonArrayQuery extends ParametrizedMigrationQuery
{
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Convert a column with "json_array(text)" type to "json_array" type on PostgreSQL >= 9.2 and Doctrine 2.5'
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
        if ($platform instanceof PostgreSQL92Platform) {
            $updateSql = 'ALTER TABLE orocrm_channel ALTER COLUMN data TYPE JSON USING data::JSON';

            $this->logQuery($logger, $updateSql);
            if (!$dryRun) {
                $this->connection->executeUpdate($updateSql);
            }
        }
    }
}
