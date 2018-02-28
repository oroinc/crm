<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_28;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateLeadConfigs implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(
                'Oro\Bundle\SalesBundle\Entity\Lead',
                'campaign',
                'datagrid',
                'is_visible',
                DatagridScope::IS_VISIBLE_FALSE
            )
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(
                'Oro\Bundle\SalesBundle\Entity\Lead',
                'campaign',
                'datagrid',
                'show_filter',
                false
            )
        );
    }
}
