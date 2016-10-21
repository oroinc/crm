<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\InstallerBundle\Migration\UpdateExtendRelationQuery;

class MigrateRelations implements Migration, RenameExtensionAwareInterface
{
    /** @var RenameExtension */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameActivityTables($schema, $queries);
        $this->updateNotes($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function renameActivityTables(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;

        $extension->renameTable($schema, $queries, 'oro_rel_46a29d199e0854fe307b0c', 'oro_rel_46a29d199e0854fe254c12');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity',
            'case_entity_81e7ef35',
            'case_entity_eafc92f2',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba69e0854fe38fbb3', 'oro_rel_c3990ba69e0854fe1d2e0c');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity',
            'case_entity_a4fb53f5',
            'case_entity_21c63d4b',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_265353709e0854fe307b0c', 'oro_rel_265353709e0854fe254c12');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity',
            'case_entity_81e7ef35',
            'case_entity_eafc92f2',
            RelationType::MANY_TO_MANY
        ));
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function updateNotes(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;
        $notes = $schema->getTable('oro_note');

        $notes->removeForeignKey('FK_BA066CE1BD1CA37');
        $extension->renameColumn($schema, $queries, $notes, 'case_entity_217e0931_id', 'case_entity_4eb2178_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_note',
            'orocrm_case',
            ['case_entity_4eb2178_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\NoteBundle\Entity\Note',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity',
            'case_entity_217e0931',
            'case_entity_4eb2178',
            RelationType::MANY_TO_ONE
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }
}
