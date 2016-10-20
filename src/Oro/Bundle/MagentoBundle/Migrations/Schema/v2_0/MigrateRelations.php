<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v2_0;

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
        $this->updateTrackingVisitEvent($schema, $queries);
        $this->updateTrackingVisit($schema, $queries);
        $this->updateNotes($schema, $queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function renameActivityTables(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;

        $extension->renameTable($schema, $queries, 'oro_rel_46a29d19784fec5f827dff', 'oro_rel_46a29d19784fec5f1a3d8f');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_14831de6',
            'customer_11e85188',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_46a29d1934e8bc9c7c8165', 'oro_rel_46a29d1934e8bc9c32a2d0');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'order_19a88871',
            'order_5f6f5774',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba634e8bc9c8dcd6f', 'oro_rel_c3990ba634e8bc9c5199de');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'order_abdeb9f6',
            'order_9df4facb',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba6784fec5f8fa9f6', 'oro_rel_c3990ba6784fec5f34369d');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_8fc2c3ea',
            'customer_a39e600f',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba6ab9127897776b0', 'oro_rel_c3990ba6ab9127896c7199');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\MagentoBundle\Entity\Cart',
            'cart_2270e8cf',
            'cart_b792365',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_26535370784fec5f827dff', 'oro_rel_26535370784fec5f1a3d8f');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_14831de6',
            'customer_11e85188',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_26535370ab91278964246c', 'oro_rel_26535370ab912789cae7ba');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\MagentoBundle\Entity\Cart',
            'cart_e94a4776',
            'cart_472b3bd9',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_2653537034e8bc9c7c8165', 'oro_rel_2653537034e8bc9c32a2d0');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'order_19a88871',
            'order_5f6f5774',
            RelationType::MANY_TO_MANY
        ));
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function updateTrackingVisitEvent(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;
        $trackingVisitEventTable = $schema->getTable('oro_tracking_visit_event');

        $trackingVisitEventTable->removeForeignKey('FK_B39EEE8F6463B7EE');
        $extension->renameColumn(
            $schema,
            $queries,
            $trackingVisitEventTable,
            'customer_bb9e15ff_id',
            'customer_2bc6a2ee_id'
        );
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_tracking_visit_event',
            'oro_magento_customer',
            ['customer_2bc6a2ee_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_bb9e15ff',
            'customer_2bc6a2ee',
            RelationType::MANY_TO_ONE
        ));

        $trackingVisitEventTable->removeForeignKey('FK_B39EEE8F7A410A1C');
        $extension->renameColumn($schema, $queries, $trackingVisitEventTable, 'cart_72e8ef17_id', 'cart_4962cb03_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_tracking_visit_event',
            'oro_magento_cart',
            ['cart_4962cb03_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
            'Oro\Bundle\MagentoBundle\Entity\Cart',
            'cart_72e8ef17',
            'cart_4962cb03',
            RelationType::MANY_TO_ONE
        ));

        $trackingVisitEventTable->removeForeignKey('FK_B39EEE8F15F9453D');
        $extension->renameColumn($schema, $queries, $trackingVisitEventTable, 'order_23867b17_id', 'order_3967254e_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_tracking_visit_event',
            'oro_magento_order',
            ['order_3967254e_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'order_23867b17',
            'order_3967254e',
            RelationType::MANY_TO_ONE
        ));

        $trackingVisitEventTable->removeForeignKey('FK_B39EEE8FE0618C12');
        $extension->renameColumn(
            $schema,
            $queries,
            $trackingVisitEventTable,
            'product_c1803ccc_id',
            'product_262abcc3_id'
        );
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_tracking_visit_event',
            'oro_magento_product',
            ['product_262abcc3_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
            'Oro\Bundle\MagentoBundle\Entity\Product',
            'product_c1803ccc',
            'product_262abcc3',
            RelationType::MANY_TO_ONE
        ));
    }


    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function updateTrackingVisit(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;
        $oroTrackingVisitTable = $schema->getTable('oro_tracking_visit');

        $oroTrackingVisitTable->removeForeignKey('FK_D204B9806C01E208');
        $extension->renameColumn(
            $schema,
            $queries,
            $oroTrackingVisitTable,
            'customer_ff3bb796_id',
            'customer_7c2d0d96_id'
        );
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_tracking_visit',
            'oro_magento_customer',
            ['customer_7c2d0d96_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TrackingBundle\Entity\TrackingVisit',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_ff3bb796',
            'customer_7c2d0d96',
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

        $notes->removeForeignKey('fk_oro_note_order_142bf5fc_id');
        $extension->renameColumn($schema, $queries, $notes, 'order_142bf5fc_id', 'order_e1ff24e2_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_note',
            'oro_magento_order',
            ['order_e1ff24e2_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\NoteBundle\Entity\Note',
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'order_142bf5fc',
            'order_e1ff24e2',
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
