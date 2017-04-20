<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_31\Query;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;

class MigrateB2bCustomersQuery extends ParametrizedMigrationQuery
{
    /** @var string */
    protected $customerColumnName;

    /**
     * @param string $customerColumnName
     */
    public function __construct($customerColumnName = '')
    {
        $this->customerColumnName = $customerColumnName;
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
     * @param bool            $dryRun
     */
    protected function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $query = 'SELECT id, name, user_owner_id, organization_id, name, createdAt, updatedAt '.
            'FROM orocrm_sales_b2bcustomer WHERE account_id IS NULL';
        $customersWithoutAccount = $this->connection->fetchAll($query);
        foreach ($customersWithoutAccount as $customer) {
            $query = 'INSERT INTO orocrm_account '.
                '(user_owner_id, organization_id, name, createdAt, updatedAt, serialized_data) '.
                'VALUES(:user_owner_id, :organization_id, :name, :createdAt, :updatedAt, :serialized_data)';
            $this->connection->executeQuery(
                $query,
                [
                    'user_owner_id' => $customer['user_owner_id'],
                    'organization_id' => $customer['organization_id'],
                    'name' => $customer['name'],
                    'createdAt' => isset($customer['createdAt']) ? $customer['createdAt'] : $customer['createdat'],
                    'updatedAt' => isset($customer['updatedAt']) ? $customer['updatedAt'] : $customer['updatedat'],
                    'serialized_data' => base64_encode(serialize(null)),
                ],
                [
                    'user_owner_id' => 'integer',
                    'organization_id' => 'integer',
                    'name' => 'string',
                    'createdAt' => 'integer',
                    'updatedAt' => 'integer',
                    'serialized_data' => 'string',
                ]
            );
            $accountId = $this->connection->lastInsertId();

            $query = 'UPDATE orocrm_sales_b2bcustomer SET account_id = :account_id WHERE id = :id';
            $this->connection->executeQuery(
                $query,
                [
                    'account_id' => $accountId,
                    'id' => $customer['id'],
                ],
                [
                    'account_id' => 'integer',
                    'id' => 'integer',
                ]
            );
        }

        $query = 'INSERT INTO orocrm_sales_customer (account_id, ' . $this->customerColumnName . ') '
            . ' SELECT account_id, id FROM orocrm_sales_b2bcustomer';

        $this->logQuery($logger, $query, [], []);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, [], []);
        }
    }
}
