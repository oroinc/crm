<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class UpdateChannelIntegrationsMode extends ParametrizedMigrationQuery
{
    protected $mode;

    public function __construct($mode)
    {
        $this->mode = $mode;
    }

    public function getDescription()
    {
        $logger = new ArrayLogger();
        $logger->info('Set Channel Integrations mods restricted');

        $this->updateChannelIntegrationsMode($logger);

        return $logger->getMessages();
    }

    public function execute(LoggerInterface $logger)
    {
        $this->updateChannelIntegrationsMode($logger, false);
    }

    protected function updateChannelIntegrationsMode(LoggerInterface $logger, $dryRun = true)
    {
        $ids = $this->getChannelIntegrations($logger);
        $updateSql = 'UPDATE oro_integration_channel SET edit_mode = :edit_mode '
            . 'WHERE id IN (:ids)';
        $params = ['ids' => $ids, 'edit_mode' => $this->mode];
        $types  = ['ids' => Connection::PARAM_INT_ARRAY, 'edit_mode' => Type::INTEGER];

        $this->logQuery($logger, $updateSql, $params, $types);

        if (!$dryRun) {
            $this->connection->executeUpdate($updateSql, $params, $types);
        }
    }

    protected function getChannelIntegrations(LoggerInterface $logger)
    {
        $sql = 'SELECT i.id FROM oro_integration_channel i '
            . 'INNER JOIN orocrm_channel c ON c.data_source_id = i.id '
           . 'WHERE c.status = :status';

        $params       = ['status' => Channel::STATUS_ACTIVE];
        $types        = ['status' => Type::INTEGER];

        $this->logQuery($logger, $sql, $params, $types);
        $integrations = $this->connection->fetchAll($sql, $params, $types);

        return array_reduce(
            $integrations,
            function($ids, $integration) {
                $ids[] = $integration['id'];
                return $ids;
            },
            []
        );
    }
}
