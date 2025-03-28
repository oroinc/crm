<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateChannelIntegrationsMode extends ParametrizedMigrationQuery
{
    /** @var int */
    protected $mode;

    /**
     * @param int $mode
     */
    public function __construct($mode)
    {
        $this->mode = $mode;
    }

    #[\Override]
    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Set Channel Integrations mods restricted');

        $this->updateChannelIntegrationsMode($logger);

        return $logger->getMessages();
    }

    #[\Override]
    public function execute(LoggerInterface $logger)
    {
        $this->updateChannelIntegrationsMode($logger, false);
    }

    protected function updateChannelIntegrationsMode(LoggerInterface $logger, $dryRun = true)
    {
        $ids = $this->getChannelIntegrations($logger);
        $updateSql = 'UPDATE oro_integration_channel SET edit_mode = :edit_mode WHERE id IN (:ids)';
        $params = ['ids' => $ids, 'edit_mode' => $this->mode];
        $types  = ['ids' => Connection::PARAM_INT_ARRAY, 'edit_mode' => Types::INTEGER];

        $this->logQuery($logger, $updateSql, $params, $types);

        if (!$dryRun) {
            $this->connection->executeStatement($updateSql, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return array
     */
    protected function getChannelIntegrations(LoggerInterface $logger)
    {
        $sql = 'SELECT i.id FROM oro_integration_channel i' .
               ' INNER JOIN orocrm_channel c ON c.data_source_id = i.id';

        $this->logQuery($logger, $sql);
        $integrations = $this->connection->fetchAllAssociative($sql);

        return array_reduce(
            $integrations,
            function ($ids, $integration) {
                $ids[] = $integration['id'];
                return $ids;
            },
            []
        );
    }
}
