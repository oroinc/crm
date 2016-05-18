<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Platforms\PostgreSQL92Platform;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class AccountNameExprIndexQuery extends ParametrizedMigrationQuery
{
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info(
            'Create additional expression index on PostgreSQL'
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
            $createIndex = 'CREATE INDEX account_name_expr_idx ON orocrm_account (lower(name))';

            $this->logQuery($logger, $createIndex);
            if (!$dryRun) {
                $this->connection->executeUpdate($createIndex);
            }
        }
    }
}
