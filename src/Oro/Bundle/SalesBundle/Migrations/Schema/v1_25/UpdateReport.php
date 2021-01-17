<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\ArrayLogger;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Psr\Log\LoggerInterface;

class UpdateReport extends ParametrizedMigrationQuery implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new self());
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
    public function doExecute(LoggerInterface $logger, $dryRun = false)
    {
        $this->updateReportAndSegments($logger, $dryRun);
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function updateReportAndSegments(LoggerInterface $logger, $dryRun)
    {
        $tables = ['oro_report', 'oro_segment'];
        foreach ($tables as $table) {
            //replace address in reports/segments where lead is a related entity
            $query = sprintf('UPDATE %s SET definition = REPLACE(definition, :old, :new);', $table);
            $params = [
                'old' => 'Lead::address+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Address',
                'new' => 'Lead::addresses+Oro\\\\Bundle\\\\SalesBundle\\\\Entity\\\\LeadAddress'
            ];
            $this->executeQuery($logger, $dryRun, $query, $params);

            //replace address entity in lead reports/segments
            $query = sprintf(
                'UPDATE %s SET definition = REPLACE(definition, :old, :new) WHERE entity = :entity;',
                $table
            );
            $params = [
                'old' => '"address+Oro\\\\Bundle\\\\AddressBundle\\\\Entity\\\\Address',
                'new' => '"addresses+Oro\\\\Bundle\\\\SalesBundle\\\\Entity\\\\LeadAddress',
                'entity' => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead'
            ];
            $this->executeQuery($logger, $dryRun, $query, $params);
        }
    }

    /**
     * @param LoggerInterface $logger
     * @param bool $dryRun
     * @param string $query
     * @param array $params
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeQuery(LoggerInterface $logger, $dryRun, $query, $params = [])
    {
        $this->logQuery($logger, $query, $params);
        if (!$dryRun) {
            $this->connection->executeStatement($query, $params);
        }
    }
}
