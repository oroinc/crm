<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v2_0;

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

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba683dfdfa4dd9a2a', 'oro_rel_c3990ba683dfdfa4a13cb3');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\ContactBundle\Entity\Contact',
            'contact_e8d5b2ba',
            'contact_6de8dc04',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_2653537083dfdfa4e84e2b', 'oro_rel_2653537083dfdfa436b4e2');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\ContactBundle\Entity\Contact',
            'contact_cdc90e7a',
            'contact_a6d273bd',
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

        $notes->removeForeignKey('FK_BA066CE14BCD64E0');
        $extension->renameColumn($schema, $queries, $notes, 'contact_6d50e87e_id', 'contact_48c5c037_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_note',
            'orocrm_contact',
            ['contact_48c5c037_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\NoteBundle\Entity\Note',
            'Oro\Bundle\ContactBundle\Entity\Contact',
            'contact_6d50e87e',
            'contact_48c5c037',
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
