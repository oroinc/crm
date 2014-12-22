<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtension;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCaseBundle implements Migration, CommentExtensionAwareInterface
{
    /** @var CommentExtension */
    protected $comment;

    /**
     * @param CommentExtension $commentExtension
     */
    public function setCommentExtension(CommentExtension $commentExtension)
    {
        $this->comment = $commentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addColumnsForCase($schema);

        $queries->addPostQuery(
            "INSERT INTO oro_comment (cs_case_id, updated_by_user_id, user_owner_id, cs_contact_id, organization_id, message, createdAt, updatedAt, cs_public, comments_type)
             SELECT case_id, updated_by_id, owner_id, contact_id, organization_id, message, createdAt, updatedAt, public, 'orocrmcasecomment'
             FROM orocrm_case_comment;

             DROP TABLE orocrm_case_comment;"
        );
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public static function addColumnsForCase(Schema $schema)
    {
        $table = $schema->getTable('oro_comment');
        $table->addColumn('cs_case_id', 'integer', ['notnull' => false]);
        $table->addColumn('cs_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('cs_public', 'boolean', ['default' => '0']);
        $table->addIndex(['cs_case_id'], 'IDX_5CD3A4BACF10D4F5', []);
        $table->addIndex(['cs_contact_id'], 'IDX_5CD3A4BAE7A1254A', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case'),
            ['cs_case_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['cs_contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
