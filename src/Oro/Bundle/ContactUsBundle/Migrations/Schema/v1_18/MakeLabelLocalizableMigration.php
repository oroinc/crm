<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_18;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\LocaleBundle\Migration\PopulateLocalizedFallbackCollectionMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Turns ContactReason labels field into localizable values relataion and removes `label` field from table
 */
class MakeLabelLocalizableMigration implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOrocrmContactReasonTitlesTable($schema);

        $queries->addQuery(new PopulateLocalizedFallbackCollectionMigrationQuery(
            'SELECT id, label as value FROM orocrm_contactus_contact_rsn',
            'INSERT INTO orocrm_contactus_contact_rsn_t (contact_reason_id, localized_value_id) VALUES (:id, :valueId)'
        ));

        $queries->addPostQuery('ALTER TABLE orocrm_contactus_contact_rsn DROP label');
    }

    protected function createOrocrmContactReasonTitlesTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_contactus_contact_rsn_t');
        $table->addColumn('contact_reason_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['contact_reason_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contactus_contact_rsn'),
            ['contact_reason_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
