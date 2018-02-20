<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_37;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveFieldQuery;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveTableQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveDeprecatedTablesAndFields implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->removeForeignKey('FK_73DB4633F5B7AF75');
        $table->dropColumn('address_id');
        $schema->dropTable('orocrm_sales_lead_status');
        $schema->dropTable('orocrm_sales_opport_status');

        $queries->addPostQuery(new RemoveTableQuery('Oro\Bundle\SalesBundle\Entity\LeadStatus'));
        $queries->addPostQuery(new RemoveTableQuery('Oro\Bundle\SalesBundle\Entity\OpportunityStatus'));
        $queries->addPostQuery(new RemoveFieldQuery(
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'address'
        ));
    }
}
