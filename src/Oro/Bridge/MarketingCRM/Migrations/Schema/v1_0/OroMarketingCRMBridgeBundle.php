<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\InstallerBundle\Migration\UpdateExtendRelationQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMarketingCRMBridgeBundle implements
    Migration,
    RenameExtensionAwareInterface
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
        self::updateTrackingVisitEvent($schema, $queries, $this->renameExtension);
        self::updateTrackingVisit($schema, $queries, $this->renameExtension);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    public static function updateTrackingVisitEvent(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        self::updateTrackingCampaign($schema, $queries, $extension);
        self::updateTrackingCustomer($schema, $queries, $extension);
        self::updateTrackingCart($schema, $queries, $extension);
        self::updateTrackingOrder($schema, $queries, $extension);
        self::updateTrackingProduct($schema, $queries, $extension);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     * @param RenameExtension $extension
     */
    public static function updateTrackingVisit(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $table = $schema->getTable('oro_tracking_visit');

        if ($table->hasColumn('customer_ff3bb796_id') && !$table->hasColumn('customer_7c2d0d96_id')) {
            if ($table->hasForeignKey('FK_D204B9806C01E208')) {
                $table->removeForeignKey('FK_D204B9806C01E208');
            }

            $extension->renameColumn($schema, $queries, $table, 'customer_ff3bb796_id', 'customer_7c2d0d96_id');
            $extension->addForeignKeyConstraint(
                $schema,
                $queries,
                'oro_tracking_visit',
                'orocrm_magento_customer',
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
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private static function updateTrackingCampaign(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $table = $schema->getTable('oro_tracking_visit_event');

        if ($table->hasColumn('campaign_cb6118ed_id') && !$table->hasColumn('campaign_a14160a8_id')) {
            if ($table->hasForeignKey('FK_B39EEE8F218EECB4')) {
                $table->removeForeignKey('FK_B39EEE8F218EECB4');
            }

            $extension->renameColumn($schema, $queries, $table, 'campaign_cb6118ed_id', 'campaign_a14160a8_id');
            $extension->addForeignKeyConstraint(
                $schema,
                $queries,
                'oro_tracking_visit_event',
                'orocrm_campaign',
                ['campaign_a14160a8_id'],
                ['id'],
                ['onDelete' => 'SET NULL']
            );

            $queries->addQuery(new UpdateExtendRelationQuery(
                'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
                'Oro\Bundle\CampaignBundle\Entity\Campaign',
                'campaign_cb6118ed',
                'campaign_a14160a8',
                RelationType::MANY_TO_ONE
            ));
        }
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private static function updateTrackingCustomer(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $table = $schema->getTable('oro_tracking_visit_event');

        if ($table->hasColumn('customer_bb9e15ff_id') && !$table->hasColumn('customer_2bc6a2ee_id')) {
            if ($table->hasForeignKey('FK_B39EEE8F6463B7EE')) {
                $table->removeForeignKey('FK_B39EEE8F6463B7EE');
            }

            $extension->renameColumn($schema, $queries, $table, 'customer_bb9e15ff_id', 'customer_2bc6a2ee_id');
            $extension->addForeignKeyConstraint(
                $schema,
                $queries,
                'oro_tracking_visit_event',
                'orocrm_magento_customer',
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
        }
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private static function updateTrackingCart(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $table = $schema->getTable('oro_tracking_visit_event');

        if ($table->hasColumn('cart_72e8ef17_id') && !$table->hasColumn('cart_4962cb03_id')) {
            if ($table->hasForeignKey('FK_B39EEE8F7A410A1C')) {
                $table->removeForeignKey('FK_B39EEE8F7A410A1C');
            }

            $extension->renameColumn($schema, $queries, $table, 'cart_72e8ef17_id', 'cart_4962cb03_id');
            $extension->addForeignKeyConstraint(
                $schema,
                $queries,
                'oro_tracking_visit_event',
                'orocrm_magento_cart',
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
        }
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private static function updateTrackingOrder(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $table = $schema->getTable('oro_tracking_visit_event');

        if ($table->hasColumn('order_23867b17_id') && !$table->hasColumn('order_3967254e_id')) {
            if ($table->hasForeignKey('FK_B39EEE8F15F9453D')) {
                $table->removeForeignKey('FK_B39EEE8F15F9453D');
            }

            $extension->renameColumn($schema, $queries, $table, 'order_23867b17_id', 'order_3967254e_id');
            $extension->addForeignKeyConstraint(
                $schema,
                $queries,
                'oro_tracking_visit_event',
                'orocrm_magento_order',
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
        }
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private static function updateTrackingProduct(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $table = $schema->getTable('oro_tracking_visit_event');

        if ($table->hasColumn('product_c1803ccc_id') && !$table->hasColumn('product_262abcc3_id')) {
            if ($table->hasForeignKey('FK_B39EEE8FE0618C12')) {
                $table->removeForeignKey('FK_B39EEE8FE0618C12');
            }

            $extension->renameColumn($schema, $queries, $table, 'product_c1803ccc_id', 'product_262abcc3_id');
            $extension->addForeignKeyConstraint(
                $schema,
                $queries,
                'oro_tracking_visit_event',
                'orocrm_magento_product',
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
    }
}
