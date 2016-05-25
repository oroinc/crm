<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateCallDurationToIntegerQuery extends ParametrizedMigrationQuery
{
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Convert a column with "time" type to "integer" type');

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
            $sql = 'ALTER TABLE orocrm_call ALTER duration TYPE INT USING duration::integer';
            $this->logQuery($logger, $sql);

            if (!$dryRun) {
                $this->connection->executeUpdate($sql);
            }
        }
    }
}
