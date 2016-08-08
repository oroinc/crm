<?php

namespace OroCRM\Bundle\SalesBundle\Migration;

use Psr\Log\LoggerInterface;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

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

    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $statusesSql = <<<SQL
INSERT INTO oro_audit_field
(audit_id, FIELD, data_type, new_datetime)
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
GROUP BY object_id
SQL;

        $params = [
            'field' => 'status',
            'statuses' => ['Closed Lost', 'Closed Won', 'Lost', 'Won'],
            'objectClass' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity'
        ];
        $types  = [
            'field' => Type::STRING,
            'statuses' => Connection::PARAM_STR_ARRAY,
            'objectClass' => Type::STRING
        ];

        $this->logQuery($logger, $statusesSql, $params, $types);

        if (!$dryRun) {
            $this->connection->executeUpdate($statusesSql, $params, $types);
        }
    }
}
