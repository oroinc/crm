<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25_2;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Exception\UnsupportedDatabasePlatformException;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Psr\Log\LoggerInterface;

class FillClosedAtField extends ParametrizedMigrationQuery
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
        $this->updateOpportunityClosedAtValue($logger, $dryRun);
        $this->insertClosedAtAuditData($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     */
    protected function insertClosedAtAuditData(LoggerInterface $logger, $dryRun)
    {
        $auditInsertSql = <<<SQL
INSERT INTO oro_audit_field
(audit_id, field, data_type, new_datetime)
SELECT
    a.id AS audit_id,
    'closedAt' AS field,
    'datetime' AS data_type,
    (
        CASE
            WHEN af.new_text IN (:statuses) THEN a.logged_at
            ELSE NULL
        END
    ) AS new_datetime
FROM oro_audit_field af
JOIN oro_audit a ON a.id = af.audit_id
INNER JOIN
(
    SELECT MAX(af1.id) id FROM oro_audit_field af1 JOIN oro_audit a1 ON a1.id = af1.audit_id WHERE
        a1.object_class = :objectClass
        AND af1.field = :field
                AND
            (
                af1.old_text IN (:statuses)
                OR
                af1.new_text IN (:statuses)
            )
        GROUP BY a1.object_id
) af1 ON af1.id = af.id
GROUP BY object_id, a.id, af.new_text
SQL;
        $params         = [
            'field'       => 'status',
            'statuses'    => ['Closed Lost', 'Closed Won', 'Lost', 'Won'],
            'objectClass' => 'Oro\Bundle\SalesBundle\Entity\Opportunity'
        ];
        $types          = [
            'field'       => Types::STRING,
            'statuses'    => Connection::PARAM_STR_ARRAY,
            'objectClass' => Types::STRING
        ];

        $this->logQuery($logger, $auditInsertSql, $params, $types);

        if (!$dryRun) {
            $this->connection->executeStatement($auditInsertSql, $params, $types);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool            $dryRun
     *
     * @throws UnsupportedDatabasePlatformException
     */
    protected function updateOpportunityClosedAtValue(LoggerInterface $logger, $dryRun)
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSqlPlatform) {
            $updateSql = <<<SQL
UPDATE orocrm_sales_opportunity o
SET closed_at = afm.logged_at
FROM 
oro_audit a
INNER JOIN
(
    SELECT
    MAX(af.audit_id) AS max_audit_id,
    MAX(am.logged_at) AS logged_at
    FROM oro_audit_field af
    INNER JOIN oro_audit am ON am.id = af.audit_id AND am.object_class = :objectClass
    WHERE af.field = :field AND af.new_text IN (:statuses)
    GROUP BY am.object_id
) afm
ON afm.max_audit_id = a.id

WHERE o.status_id IN (:status_ids) and 
a.object_id = o.id AND a.object_class = :objectClass
SQL;
        } elseif ($platform instanceof MySqlPlatform) {
            $updateSql = <<<SQL
UPDATE orocrm_sales_opportunity o
INNER JOIN oro_audit a ON a.object_id = o.id AND a.object_class = :objectClass
INNER JOIN
(
    SELECT
    MAX(af.audit_id) AS max_audit_id,
    MAX(am.logged_at) AS logged_at
    FROM oro_audit_field af
    INNER JOIN oro_audit am ON am.id = af.audit_id AND am.object_class = :objectClass
    WHERE af.field = :field AND af.new_text IN (:statuses)
    GROUP BY am.object_id
) afm
ON afm.max_audit_id = a.id
SET o.closed_at = afm.logged_at
WHERE o.status_id IN (:status_ids)
SQL;
        } else {
            throw new UnsupportedDatabasePlatformException(
                sprintf('Platform %s is not supported', $platform->getName())
            );
        }
        $params = [
            'field'       => 'status',
            'statuses'    => ['Closed Lost', 'Closed Won', 'Lost', 'Won'],
            'objectClass' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'status_ids'  => [Opportunity::STATUS_WON, Opportunity::STATUS_LOST]
        ];
        $types  = [
            'field'       => Types::STRING,
            'statuses'    => Connection::PARAM_STR_ARRAY,
            'objectClass' => Types::STRING,
            'status_ids'  => Connection::PARAM_STR_ARRAY
        ];

        $this->logQuery($logger, $updateSql, $params, $types);

        if (!$dryRun) {
            $this->connection->executeStatement($updateSql, $params, $types);
        }
    }
}
