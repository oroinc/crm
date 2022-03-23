<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_41;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class AddLeadEmailToOroEmailAddress implements Migration, DatabasePlatformAwareInterface
{
    use DatabasePlatformAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addOwnerToOroEmailAddress($schema);
        if ($this->platform instanceof PostgreSqlPlatform) {
            $queries->addPostQuery(new SqlMigrationQuery(
                'CREATE INDEX IF NOT EXISTS idx_lead_email_ci ON orocrm_sales_lead_email (LOWER(email))'
            ));
        }
    }

    private function addOwnerToOroEmailAddress(Schema $schema): void
    {
        $table = $schema->getTable('oro_email_address');
        if (!$table->hasColumn('owner_lead_id')) {
            $table->addColumn('owner_lead_id', 'integer', ['notnull' => false]);
            $table->addIndex(['owner_lead_id']);
            $table->addForeignKeyConstraint(
                $schema->getTable('orocrm_sales_lead'),
                ['owner_lead_id'],
                ['id']
            );
        }
    }
}
