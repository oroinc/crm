<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_0;

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
        $this->updateAttachments($schema, $queries);
        $this->updateNotes($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function renameActivityTables(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;

        $extension->renameTable($schema, $queries, 'oro_rel_46a29d1988a3cef5d4431f', 'oro_rel_46a29d1988a3cef53c57d4');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'lead_e5b9c444',
            'lead_23c40e3e',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_46a29d19e65dd9d3815d62', 'oro_rel_46a29d19e65dd9d390636c');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
            'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
            'b2b_customer_22d81e5c',
            'b2b_customer_88d7394f',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba65154c0019aab45', 'oro_rel_c3990ba65154c0069aa16e');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'opportunity_c8bd867a',
            'opportunity_18f1cc2e',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba688a3cef599ef6a', 'oro_rel_c3990ba688a3cef5c8efa6');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'lead_272269fb',
            'lead_4506e71e',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba6e65dd9d32a889c', 'oro_rel_c3990ba6e65dd9d37deb67');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
            'b2b_customer_2bf513a9',
            'b2b_customer_fbb959fd',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_265353705154c0055a16fb', 'oro_rel_265353705154c0033bfb48');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'opportunity_c1908b8f',
            'opportunity_6b9fac9c',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_2653537088a3cef5d4431f', 'oro_rel_2653537088a3cef53c57d4');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'lead_e5b9c444',
            'lead_23c40e3e',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_26535370e65dd9d3815d62', 'oro_rel_26535370e65dd9d390636c');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
            'b2b_customer_22d81e5c',
            'b2b_customer_88d7394f',
            RelationType::MANY_TO_MANY
        ));
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function updateAttachments(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;
        $attachments = $schema->getTable('oro_attachment');

        $attachments->removeForeignKey('FK_FA0FE081D449B7E7');
        $extension->renameColumn($schema, $queries, $attachments, 'opportunity_ec95a95f_id', 'opportunity_f89bd07c_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_attachment',
            'orocrm_sales_opportunity',
            ['opportunity_f89bd07c_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\AttachmentBundle\Entity\Attachment',
            'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'opportunity_ec95a95f',
            'opportunity_f89bd07c',
            RelationType::MANY_TO_ONE
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

        $notes->removeForeignKey('FK_BA066CE1D449B7E7');
        $extension->renameColumn($schema, $queries, $notes, 'opportunity_ec95a95f_id', 'opportunity_f89bd07c_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_note',
            'orocrm_sales_opportunity',
            ['opportunity_f89bd07c_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\NoteBundle\Entity\Note',
            'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'opportunity_ec95a95f',
            'opportunity_f89bd07c',
            RelationType::MANY_TO_ONE
        ));

        $notes->removeForeignKey('fk_oro_note_lead_5b29b7d2_id');
        $extension->renameColumn($schema, $queries, $notes, 'lead_5b29b7d2_id', 'lead_ac2d73a_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_note',
            'orocrm_sales_lead',
            ['lead_ac2d73a_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\NoteBundle\Entity\Note',
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'lead_5b29b7d2',
            'lead_ac2d73a',
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
