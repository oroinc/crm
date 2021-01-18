<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_31\Query;

use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

class UpdateOpportunitiesQuery extends ParametrizedMigrationQuery
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
        $query = 'UPDATE orocrm_sales_opportunity o SET customer_association_id = '
            . '(SELECT id FROM orocrm_sales_customer c WHERE c.' . $this->customerColumnName . ' = o.customer_id)';

        $this->logQuery($logger, $query, [], []);
        if (!$dryRun) {
            $this->connection->executeStatement($query, [], []);
        }
    }
}
