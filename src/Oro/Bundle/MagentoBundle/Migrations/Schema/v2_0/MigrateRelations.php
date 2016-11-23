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

        if ($schema->hasTable('oro_rel_46a29d1934e8bc9c7c8165')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d1934e8bc9c7c8165',
                'oro_rel_46a29d1934e8bc9c32a2d0'
            );
            $queries->addQuery(
                new UpdateExtendRelationQuery(
                    'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                    'Oro\Bundle\MagentoBundle\Entity\Order',
                    'order_19a88871',
                    'order_5f6f5774',
                    RelationType::MANY_TO_MANY
                )
            );
        }

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
            'orocrm_magento_order',
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
