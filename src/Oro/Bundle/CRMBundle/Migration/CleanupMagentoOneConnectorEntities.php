<?php
declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\Migration;

use Oro\Bundle\ActivityBundle\Migration\RemoveActivityAssociationQuery;
use Oro\Bundle\ActivityListBundle\Migration\RemoveActivityListAssociationQuery;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CallBundle\Entity\Call;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\NoteBundle\Entity\Note;
use Oro\Bundle\SalesBundle\Migration\RemoveCustomerAssociationQuery;
use Oro\Bundle\TaskBundle\Entity\Task;
use Oro\Bundle\TrackingBundle\Migration\RemoveVisitEventAssociationQuery;
use Oro\Bundle\TrackingBundle\Migration\RemoveVisitIdentifierAssociationQuery;

/**
 * Removes entity configs of MagentoBundle entities and all their associations to other entities.
 * The data is not removed from the database, except for the relation columns in other entity tables
 * and many-to-many relation tables.
 */
class CleanupMagentoOneConnectorEntities
{
    /**
     * Removes entity configs of MagentoBundle entities and all their associations to other entities.
     * The data is not removed from the database, except for the relation columns in other entity tables
     * and many-to-many relation tables if an instance of an updatable schema is provided to this method.
     *
     * @return ParametrizedMigrationQuery[]
     *
     * @noinspection ClassConstantCanBeUsedInspection
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getQueries(bool $dropRelationColumnsAndTables): array
    {
        return [
            /////////////////////////////////////////////////////////////
            // 1. Remove activity associations of MagentoBundle entities.
            /////////////////////////////////////////////////////////////

            // Undo $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_magento_order');
            new RemoveActivityAssociationQuery(
                Note::class,
                'Oro\Bundle\MagentoBundle\Entity\Order',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_customer');
            new RemoveActivityAssociationQuery(
                Email::class,
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_magento_credit_memo');
            new RemoveActivityAssociationQuery(
                Note::class,
                'Oro\Bundle\MagentoBundle\Entity\CreditMemo',
                $dropRelationColumnsAndTables
            ),

            // Undo $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_order');
            new RemoveActivityAssociationQuery(
                Email::class,
                'Oro\Bundle\MagentoBundle\Entity\Order',
                $dropRelationColumnsAndTables
            ),

            // Undo $activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_cart');
            new RemoveActivityAssociationQuery(
                Email::class,
                'Oro\Bundle\MagentoBundle\Entity\Cart',
                $dropRelationColumnsAndTables
            ),

            // Undo oro_calendar_event => orocrm_magento_customer
            new RemoveActivityAssociationQuery(
                CalendarEvent::class,
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                $dropRelationColumnsAndTables
            ),

            // Undo oro_calendar_event => orocrm_magento_order
            new RemoveActivityAssociationQuery(
                CalendarEvent::class,
                'Oro\Bundle\MagentoBundle\Entity\Order',
                $dropRelationColumnsAndTables
            ),

            // Undo 'orocrm_call' => 'orocrm_magento_customer'
            new RemoveActivityAssociationQuery(
                Call::class,
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                $dropRelationColumnsAndTables
            ),

            // Undo 'orocrm_call' => 'orocrm_magento_order'
            new RemoveActivityAssociationQuery(
                Call::class,
                'Oro\Bundle\MagentoBundle\Entity\Order',
                $dropRelationColumnsAndTables
            ),

            // Undo 'orocrm_call' => 'orocrm_magento_cart'
            new RemoveActivityAssociationQuery(
                Call::class,
                'Oro\Bundle\MagentoBundle\Entity\Cart',
                $dropRelationColumnsAndTables
            ),

            // Undo 'orocrm_task' => 'orocrm_magento_customer'
            new RemoveActivityAssociationQuery(
                Task::class,
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                $dropRelationColumnsAndTables
            ),

            // Undo 'orocrm_task' => 'orocrm_magento_order'
            new RemoveActivityAssociationQuery(
                Task::class,
                'Oro\Bundle\MagentoBundle\Entity\Order',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->activityListExtension->addActivityListAssociation($schema, 'orocrm_magento_credit_memo');
            new RemoveActivityListAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\CreditMemo',
                $dropRelationColumnsAndTables
            ),

            // Undo $activityListExtension->addActivityListAssociation($schema, 'orocrm_magento_cart');
            new RemoveActivityListAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Cart',
                $dropRelationColumnsAndTables
            ),

            // Undo $activityListExtension->addActivityListAssociation($schema, 'orocrm_magento_order');
            new RemoveActivityListAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Order',
                $dropRelationColumnsAndTables
            ),

            // origin unknown
            new RemoveActivityListAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                $dropRelationColumnsAndTables
            ),

            /////////////////////////////////////////////////////////////
            // 2. Remove customer associations of MagentoBundle entities.
            /////////////////////////////////////////////////////////////

            // Undo $this->customerExtension->addCustomerAssociation($schema, 'orocrm_magento_customer');
            new RemoveCustomerAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                'orocrm_magento_customer',
                $dropRelationColumnsAndTables
            ),

            /////////////////////////////////////////////////////////////
            // 3. Remove tracking associations of MagentoBundle entities.
            /////////////////////////////////////////////////////////////

            // Undo $this->identifierEventExtension->addIdentifierAssociation($schema, 'orocrm_magento_customer');
            new RemoveVisitIdentifierAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                'orocrm_magento_customer',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_cart');
            new RemoveVisitEventAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Cart',
                'orocrm_magento_cart',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_customer');
            new RemoveVisitEventAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Customer',
                'orocrm_magento_customer',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_order');
            new RemoveVisitEventAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Order',
                'orocrm_magento_order',
                $dropRelationColumnsAndTables
            ),

            // Undo $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_product');
            new RemoveVisitEventAssociationQuery(
                'Oro\Bundle\MagentoBundle\Entity\Product',
                'orocrm_magento_product',
                $dropRelationColumnsAndTables
            ),

            //////////////////////////////////////////////////////////
            // 4. Remove entity configs, fields, index, log, log diff.
            //////////////////////////////////////////////////////////

            new CleanupMagentoOneConnectorEntityConfigsQuery()
        ];
    }
}
