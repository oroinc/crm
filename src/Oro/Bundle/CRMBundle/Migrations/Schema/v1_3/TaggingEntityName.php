<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class TaggingEntityName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateTaggingEntityName($queries);
    }

    public static function updateTaggingEntityName(QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_tag_tagging',
            'entity_name',
            'OroCRM',
            'Oro'
        ));
    }
}
