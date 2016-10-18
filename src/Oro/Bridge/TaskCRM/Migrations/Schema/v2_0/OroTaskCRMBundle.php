<?php

namespace Oro\Bridge\TaskCRM\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\InstallerBundle\Migration\UpdateExtendRelationQuery;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;

class OroTaskCRMBundle implements Migration, RenameExtensionAwareInterface
{
    /** @var RenameExtension */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameActivityTables($schema, $queries);
        $this->updateComment($schema, $queries);
        $this->updateTableField($queries);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    private function renameActivityTables(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741be65dd9d3815d62', 'oro_rel_f24c741be65dd9d390636c');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
            'b2b_customer_22d81e5c',
            'b2b_customer_88d7394f',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741bb28b6f386b70ee', 'oro_rel_f24c741bb28b6f3865ba50');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\AccountBundle\Entity\Account',
            'account_89f0f6f',
            'account_638472a8',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741b9e0854fe307b0c', 'oro_rel_f24c741b9e0854fe254c12');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity',
            'case_entity_81e7ef35',
            'case_entity_eafc92f2',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741b88a3cef5d4431f', 'oro_rel_f24c741b88a3cef53c57d4');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'lead_e5b9c444',
            'lead_23c40e3e',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741b83dfdfa4e84e2b', 'oro_rel_f24c741b83dfdfa436b4e2');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\ContactBundle\Entity\Contact',
            'contact_cdc90e7a',
            'contact_a6d273bd',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741b784fec5f827dff', 'oro_rel_f24c741b784fec5f1a3d8f');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_14831de6',
            'customer_11e85188',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741b5154c0055a16fb', 'oro_rel_f24c741b5154c0033bfb48');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'opportunity_c1908b8f',
            'opportunity_6b9fac9c',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_f24c741b34e8bc9c7c8165', 'oro_rel_f24c741b34e8bc9c32a2d0');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TaskBundle\Entity\Task',
            'Oro\Bundle\MagentoBundle\Entity\Order',
            'order_19a88871',
            'order_5f6f5774',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_c3990ba6f24c741bfb63ea', 'oro_rel_c3990ba6f24c741b1d920a');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\ActivityListBundle\Entity\ActivityList',
            'Oro\Bundle\TaskBundle\Entity\Task',
            'task_275b26d7',
            'task_dec81c8b',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_26535370f24c741b540020', 'oro_rel_26535370f24c741be77458');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\EmailBundle\Entity\Email',
            'Oro\Bundle\TaskBundle\Entity\Task',
            'task_6b093e6b',
            'task_1a1af651',
            RelationType::MANY_TO_MANY
        ));
    }

    private function updateComment(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;
        $comment = $schema->getTable('oro_comment');

        $comment->removeForeignKey('FK_5CD3A4BAC06839D3');
        $extension->renameColumn($schema, $queries, $comment, 'task_f06ab819_id', 'task_c50a6a28_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_comment',
            'orocrm_task',
            ['task_c50a6a28_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CommentBundle\Entity\Comment',
            'Oro\Bundle\TaskBundle\Entity\Task',
            'task_f06ab819',
            'task_c50a6a28',
            RelationType::MANY_TO_ONE
        ));
    }

    /**
     * @param QueryBag $queries
     */
    protected function updateTableField(QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_activity_list',
            'related_activity_class',
            'OroCRM',
            'Oro'
        ));

        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_email_template',
            'entityname',
            'OroCRM',
            'Oro'
        ));

        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_email_template',
            'content',
            'OroCRM',
            'Oro'
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
