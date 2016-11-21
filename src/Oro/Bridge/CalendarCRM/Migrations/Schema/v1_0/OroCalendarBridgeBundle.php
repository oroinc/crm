<?php

namespace Oro\Bridge\CalendarCRM\Migrations\Schema\v1_0;

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
     * @param RenameExtension $extension
     */
    public static function renameActivityTables(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        self::renameAccountRelated($schema, $queries, $extension);
        self::renameCaseRelated($schema, $queries, $extension);
        self::renameContactRelated($schema, $queries, $extension);
        self::renameMagentoRelated($schema, $queries, $extension);
        self::renameSalesRelated($schema, $queries, $extension);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    private static function renameAccountRelated(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        if ($schema->hasTable('oro_rel_46a29d19b28b6f386b70ee')
            && !$schema->hasTable('oro_rel_46a29d19b28b6f3865ba50')) {
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
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    private static function renameCaseRelated(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        if ($schema->hasTable('oro_rel_46a29d199e0854fe307b0c')
            && !$schema->hasTable('oro_rel_46a29d199e0854fe254c12')) {
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
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    private static function renameContactRelated(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        if ($schema->hasTable('oro_rel_46a29d1983dfdfa4e84e2b')
            && !$schema->hasTable('oro_rel_46a29d1983dfdfa436b4e2')) {
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
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    private static function renameMagentoRelated(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        if ($schema->hasTable('oro_rel_46a29d19784fec5f827dff')
            && !$schema->hasTable('oro_rel_46a29d19784fec5f1a3d8f')) {
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

        if ($schema->hasTable('oro_rel_46a29d1934e8bc9c7c8165')
            && !$schema->hasTable('oro_rel_46a29d1934e8bc9c32a2d0')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d1934e8bc9c7c8165',
                'oro_rel_46a29d1934e8bc9c32a2d0'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\MagentoBundle\Entity\Order',
                'order_19a88871',
                'order_5f6f5774',
                RelationType::MANY_TO_MANY
            ));
        }
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    private static function renameSalesRelated(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        if ($schema->hasTable('oro_rel_46a29d195154c0055a16fb')
            && !$schema->hasTable('oro_rel_46a29d195154c0033bfb48')) {
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

        if ($schema->hasTable('oro_rel_46a29d1988a3cef5d4431f')
            && !$schema->hasTable('oro_rel_46a29d1988a3cef53c57d4')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d1988a3cef5d4431f',
                'oro_rel_46a29d1988a3cef53c57d4'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\SalesBundle\Entity\Lead',
                'lead_e5b9c444',
                'lead_23c40e3e',
                RelationType::MANY_TO_MANY
            ));
        }

        if ($schema->hasTable('oro_rel_46a29d19e65dd9d3815d62')
            && !$schema->hasTable('oro_rel_46a29d19e65dd9d390636c')) {
            $extension->renameTable(
                $schema,
                $queries,
                'oro_rel_46a29d19e65dd9d3815d62',
                'oro_rel_46a29d19e65dd9d390636c'
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
                'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
                'b2b_customer_22d81e5c',
                'b2b_customer_88d7394f',
                RelationType::MANY_TO_MANY
            ));
        }
    }
}
