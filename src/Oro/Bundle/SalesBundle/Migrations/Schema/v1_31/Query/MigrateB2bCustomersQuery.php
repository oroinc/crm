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
        $query = 'INSERT INTO orocrm_sales_customer (account_id, ' . $this->customerColumnName . ') '
            . ' SELECT account_id, id FROM orocrm_sales_b2bcustomer';

        $this->logQuery($logger, $query, [], []);
        if (!$dryRun) {
            $this->connection->executeUpdate($query, [], []);
        }
    }
}
