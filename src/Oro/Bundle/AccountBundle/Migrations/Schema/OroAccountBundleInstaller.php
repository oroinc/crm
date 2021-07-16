<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AccountBundle\Migrations\Schema\v1_10\InheritanceActivityTargets;
use Oro\Bundle\AccountBundle\Migrations\Schema\v1_11\AccountNameExprIndexQuery;
use Oro\Bundle\AccountBundle\Migrations\Schema\v1_8\AddReferredBy;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroAccountBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    AttachmentExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_14_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmAccountTable($schema, $queries);
        $this->createOrocrmAccountToContactTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmAccountForeignKeys($schema);
        $this->addOrocrmAccountToContactForeignKeys($schema);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_account');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_account');
        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            'orocrm_account',
            [
                'image/*',
                'application/pdf',
                'application/zip',
                'application/x-gzip',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ],
            2
        );
        InheritanceActivityTargets::addInheritanceTargets($schema, $this->activityListExtension);

        // update to 1.8
        $addReferredBy = new AddReferredBy();
        $addReferredBy->up($schema, $queries);
    }

    /**
     * Create oro_account table
     */
    protected function createOrocrmAccountTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('orocrm_account');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('default_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->addColumn(
            'extend_description',
            'text',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true, 'autoescape' => false],
                    'dataaudit' => ['auditable' => true],
                    'form'      => ['type' => OroResizeableRichTextType::class],
                    'view'      => ['type' => 'html'],
                ]
            ]
        );
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'IDX_7166D3719EB185F9', []);
        $table->addIndex(['organization_id'], 'IDX_7166D37132C8A3DE', []);
        $table->addIndex(['default_contact_id'], 'IDX_7166D371AF827129', []);
        $table->addIndex(['name', 'id'], 'account_name_idx', []);

        $queries->addPostQuery(new AccountNameExprIndexQuery());
    }

    /**
     * Create oro_account_to_contact table
     */
    protected function createOrocrmAccountToContactTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_account_to_contact');
        $table->addColumn('account_id', 'integer', []);
        $table->addColumn('contact_id', 'integer', []);
        $table->setPrimaryKey(['account_id', 'contact_id']);
        $table->addIndex(['account_id'], 'IDX_65B8FBEC9B6B5FBA', []);
        $table->addIndex(['contact_id'], 'IDX_65B8FBECE7A1254A', []);
    }

    /**
     * Add oro_account foreign keys.
     */
    protected function addOrocrmAccountForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_account');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['default_contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_account_to_contact foreign keys.
     */
    protected function addOrocrmAccountToContactForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_account_to_contact');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
