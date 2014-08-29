<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

use Psr\Log\LoggerInterface;

class MigrateAccountRelations extends ParametrizedMigrationQuery
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
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $logger->info('Create b2b customers for leads and opportunities');
        $query  = <<<DQL
INSERT INTO orocrm_sales_b2bcustomer (name, account_id, user_owner_id, contact_id, createdAt, updatedAt)
SELECT
    loc.recordName, loc.accountId, loc.ownerId, loc.contactId, :currentDateTime, :currentDateTime
FROM (
        SELECT
            l.name recordName, l.account_id accountId, l.user_owner_id ownerId, l.contact_id contactId
        FROM orocrm_sales_lead l
        WHERE l.account_id IS NOT NULL
    UNION
        SELECT
            o.name recordName, o.account_id accountId, o.user_owner_id ownerId, o.contact_id contactId
        FROM orocrm_sales_opportunity o
        WHERE o.account_id IS NOT NULL
)  loc
DQL;
        $params = ['currentDateTime' => $date];
        $types  = ['currentDateTime' => Type::DATETIME];

        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, $params, $types);
        }

        $logger->info('Assign leads and opportunities to new b2b customers');

    }
}
