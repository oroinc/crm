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
        self::addComment($schema, $this->comment);

        self::addColumnsForCase($schema);

        $queries->addPostQuery(
            "INSERT INTO oro_comment (case_id, updated_by_user_id, user_owner_id, contact_id, organization_id, message, createdAt, updatedAt, public, comments_type)
             SELECT case_id, updated_by_id, owner_id, contact_id, organization_id, message, createdAt, updatedAt, public, 'orocrmcasecomment'
             FROM orocrm_case_comment;

             DROP TABLE orocrm_case_comment;"
        );
    }

    /**
     * @param Schema           $schema
     * @param CommentExtension $commentExtension
     */
    public static function addComment(Schema $schema, CommentExtension $commentExtension)
    {
        $commentExtension->addCommentAssociation($schema, 'orocrm_case');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public static function addColumnsForCase(Schema $schema)
    {
        $table = $schema->getTable('oro_comment');
        $table->addColumn('case_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('public', 'boolean', ['default' => '0']);
        $table->addIndex(['case_id'], 'IDX_5CD3A4BACF10D4F5', []);
        $table->addIndex(['contact_id'], 'IDX_5CD3A4BAE7A1254A', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case'),
            ['case_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
