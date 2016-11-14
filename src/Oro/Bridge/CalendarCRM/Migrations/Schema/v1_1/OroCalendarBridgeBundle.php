<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\InstallerBundle\Migration\UpdateExtendRelationQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCalendarBridgeBundle implements Migration, RenameExtensionAwareInterface
{
    /** @var RenameExtension */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::renameActivityTables($schema, $queries, $this->renameExtension);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    public static function renameActivityTables(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        // Execute only if tables were now renamed already

        // AccountBundle
        if ($schema->hasTable('oro_rel_46a29d19b28b6f386b70ee')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d19b28b6f386b70ee',
                'oro_rel_46a29d19b28b6f3865ba50'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\AccountBundle\Entity\Account',
                'account_89f0f6f',
                'account_638472a8',
                RelationType::MANY_TO_MANY
            ));
        }

        // CaseBundle
        if ($schema->hasTable('oro_rel_46a29d199e0854fe307b0c')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d199e0854fe307b0c',
                'oro_rel_46a29d199e0854fe254c12'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\CaseBundle\Entity\CaseEntity',
                'case_entity_81e7ef35',
                'case_entity_eafc92f2',
                RelationType::MANY_TO_MANY
            ));
        }

        // ContactBundle
        if ($schema->hasTable('oro_rel_46a29d1983dfdfa4e84e2b')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d1983dfdfa4e84e2b',
                'oro_rel_46a29d1983dfdfa436b4e2'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\ContactBundle\Entity\Contact',
                'contact_cdc90e7a',
                'contact_a6d273bd',
                RelationType::MANY_TO_MANY
            ));
        }

        // MagentoBundle
        if ($schema->hasTable('oro_rel_46a29d19784fec5f827dff')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d19784fec5f827dff',
                'oro_rel_46a29d19784fec5f1a3d8f'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                'customer_14831de6',
                'customer_11e85188',
                RelationType::MANY_TO_MANY
            ));
        }

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

        // SalesBundle
        if ($schema->hasTable('oro_rel_46a29d195154c0055a16fb')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d195154c0055a16fb',
                'oro_rel_46a29d195154c0033bfb48'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\SalesBundle\Entity\Opportunity',
                'opportunity_c1908b8f',
                'opportunity_6b9fac9c',
                RelationType::MANY_TO_MANY
            ));
        }
    }
}
