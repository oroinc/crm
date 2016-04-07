<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_42;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RevertEnumIdentity implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $entityName = ExtendHelper::buildEnumValueClassName('mage_subscr_status');

        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery($entityName, 'id', 'importexport', 'identity', true)
        );
        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery($entityName, 'name', 'importexport', 'identity', null)
        );
    }
}
