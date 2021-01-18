<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Types\Types;
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
        $this->createCustomerForLeadsAndOpportunities($logger, $dryRun);
        $this->migrateAccountExtendedFieldsValues($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function createCustomerForLeadsAndOpportunities(LoggerInterface $logger, $dryRun)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $logger->info('Create b2b customers for leads and opportunities');
        $query = <<<DQL
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
        $types  = ['currentDateTime' => Types::DATETIME_MUTABLE];

        $this->logQuery($logger, $query, $params, $types);
        if (!$dryRun) {
            $this->connection->executeStatement($query, $params, $types);
        }

        $logger->info('Assign leads and opportunities to new b2b customers');
        $subSelect = <<<DQL
SELECT bc.id
FROM orocrm_sales_b2bcustomer bc
WHERE
    bc.name = mainEntity.name
    AND bc.account_id = mainEntity.account_id
    AND bc.user_owner_id = mainEntity.user_owner_id
LIMIT 1
DQL;
        $leadUpdateQuery = <<<DQL
UPDATE orocrm_sales_lead mainEntity SET customer_id = (%1\$s) WHERE EXISTS (%1\$s)
DQL;
        $opportunityUpdateQuery = <<<DQL
UPDATE orocrm_sales_opportunity mainEntity SET customer_id = (%1\$s) WHERE EXISTS (%1\$s)
DQL;

        $leadUpdateQuery        = sprintf($leadUpdateQuery, $subSelect);
        $opportunityUpdateQuery = sprintf($opportunityUpdateQuery, $subSelect);
        $this->logQuery($logger, $leadUpdateQuery);
        $this->logQuery($logger, $opportunityUpdateQuery);
        if (!$dryRun) {
            $this->connection->executeStatement($leadUpdateQuery);
            $this->connection->executeStatement($opportunityUpdateQuery);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function migrateAccountExtendedFieldsValues(LoggerInterface $logger, $dryRun)
    {
        $query = <<<DQL
UPDATE orocrm_sales_b2bcustomer bc
SET website = (%s), employees = (%s), ownership = (%s), ticker_symbol = (%s), rating = (%s)
WHERE EXISTS (%s)
DQL;
        $query = sprintf(
            $query,
            $this->getFieldSubSelect('extend_website'),
            $this->getFieldSubSelect('extend_employees'),
            $this->getFieldSubSelect('extend_ownership'),
            $this->getFieldSubSelect('extend_ticker_symbol'),
            $this->getFieldSubSelect('extend_rating'),
            $this->getFieldSubSelect('id')
        );

        $this->logQuery($logger, $query);
        if (!$dryRun) {
            $this->connection->executeStatement($query);
        }
    }

    /**
     * @param string $fieldName
     *
     * @return string
     */
    private function getFieldSubSelect($fieldName)
    {
        return sprintf('SELECT a.%s from orocrm_account a WHERE a.id = bc.account_id', $fieldName);
    }
}
