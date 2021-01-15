<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Updated object_class column length according to
 * {@see \Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation::$objectClass} field metadata change
 */
class UpdateObjectClassFieldLength implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_case_source_trans');
        $table->changeColumn('object_class', ['length' => 191]);

        $table = $schema->getTable('orocrm_case_status_trans');
        $table->changeColumn('object_class', ['length' => 191]);

        $table = $schema->getTable('orocrm_case_priority_trans');
        $table->changeColumn('object_class', ['length' => 191]);
    }
}
