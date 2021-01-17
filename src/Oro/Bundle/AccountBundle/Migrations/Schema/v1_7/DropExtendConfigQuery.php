<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_7;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class DropExtendConfigQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Drop extend config values');
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
        $updateSql = <<<DQL
             DELETE FROM oro_entity_config_field WHERE field_name IN (?, ?, ?, ?, ?, ?, ?)
             AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = ?);
DQL;

        $params = [
            'extend_website',
            'extend_employees',
            'extend_ownership',
            'extend_ticker_symbol',
            'extend_rating',
            'shippingAddress',
            'billingAddress',
            'Oro\\Bundle\\AccountBundle\\Entity\\Account',
        ];

        $this->logQuery($logger, $updateSql, $params);
        if (!$dryRun) {
            $this->connection->executeStatement($updateSql, $params);
        }
    }
}
