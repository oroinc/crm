<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25_6;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddOpportunityStatus implements Migration, OutdatedExtendExtensionAwareInterface
{
    use OutdatedExtendExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            sprintf(
                'UPDATE %s SET name = :name WHERE id = :id',
                $this->outdatedExtendExtension::generateEnumTableName('opportunity_status')
            ),
            ['id' => 'in_progress', 'name' => 'Open'],
            ['id' => Types::STRING, 'name' => Types::STRING]
        ));
    }
}
