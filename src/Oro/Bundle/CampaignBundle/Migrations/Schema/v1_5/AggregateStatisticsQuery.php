<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Connection;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ConnectionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\MigrationQuery;
use Psr\Log\LoggerInterface;

class AggregateStatisticsQuery implements MigrationQuery, ConnectionAwareInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

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
     * @param bool $dryRun
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $duplicateEntitiesQuery = 'SELECT
                DISTINCT t2.id
            FROM
                orocrm_campaign_email_stats AS t1
            LEFT JOIN orocrm_campaign_email_stats AS t2
                ON t1.email_campaign_id = t2.email_campaign_id
                AND t1.marketing_list_item_id = t2.marketing_list_item_id
                AND t2.id > t1.id
            WHERE t2.id IS NOT NULL';

        // Done in 2 queries for cross DB support.
        $idsToRemove = array_map(
            function ($item) {
                if (is_array($item) && array_key_exists('id', $item)) {
                    return $item['id'];
                }

                return null;
            },
            $this->connection->fetchAll($duplicateEntitiesQuery)
        );

        if ($idsToRemove) {
            $query = 'DELETE FROM orocrm_campaign_email_stats WHERE id IN (?)';
            $logger->info($query);
            if (!$dryRun) {
                $this->connection->executeQuery($query, [$idsToRemove], [Connection::PARAM_INT_ARRAY]);
            }
        }
    }
}
