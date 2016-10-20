<?php

namespace Oro\Bridge\CallCRM\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\InstallerBundle\Migration\UpdateExtendRelationQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;

class OroCallCRMBridgeBundle implements Migration, RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
    private $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->renameActivityTables($schema, $queries);
        $this->updateComment($schema, $queries);
    }

    private function renameActivityTables(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc8000e65dd9d3815d62', 'oro_rel_6cbc8000e65dd9d390636c');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
            'b2b_customer_22d81e5c',
            'b2b_customer_88d7394f',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc8000b28b6f386b70ee', 'oro_rel_6cbc8000b28b6f3865ba50');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\AccountBundle\Entity\Account',
            'account_89f0f6f',
            'account_638472a8',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc8000ab91278964246c', 'oro_rel_6cbc8000ab912789cae7ba');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\MagentoBundle\Entity\Cart',
            'cart_e94a4776',
            'cart_472b3bd9',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc80009e0854fe307b0c', 'oro_rel_6cbc80009e0854fe254c12');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\CaseBundle\Entity\CaseEntity',
            'case_entity_81e7ef35',
            'case_entity_eafc92f2',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc800088a3cef5d4431f', 'oro_rel_6cbc800088a3cef53c57d4');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\SalesBundle\Entity\Lead',
            'lead_e5b9c444',
            'lead_23c40e3e',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc800083dfdfa4e84e2b', 'oro_rel_6cbc800083dfdfa436b4e2');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\ContactBundle\Entity\Contact',
            'contact_cdc90e7a',
            'contact_a6d273bd',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc8000784fec5f827dff', 'oro_rel_6cbc8000784fec5f1a3d8f');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            'customer_14831de6',
            'customer_11e85188',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc80005154c0055a16fb', 'oro_rel_6cbc80005154c0033bfb48');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'opportunity_c1908b8f',
            'opportunity_6b9fac9c',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc800050ef1ed9f9fe7f', 'oro_rel_6cbc800050ef1ed9f45d78');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
            'Oro\Bundle\ContactUsBundle\Entity\ContactRequest',
            'contact_request_a223cce9',
            'contact_request_4e3a1184',
            RelationType::MANY_TO_MANY
        ));

        $extension->renameTable($schema, $queries, 'oro_rel_6cbc800034e8bc9c7c8165', 'oro_rel_6cbc800034e8bc9c32a2d0');
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CallBundle\Entity\Call',
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
    private function updateComment(Schema $schema, QueryBag $queries)
    {
        $extension = $this->renameExtension;
        $comment = $schema->getTable('oro_comment');

        $comment->removeForeignKey('FK_5CD3A4BAD655E33D');
        $extension->renameColumn($schema, $queries, $comment, 'call_74d3684c_id', 'call_41b3ba7d_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_comment',
            'oro_call',
            ['call_41b3ba7d_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\CommentBundle\Entity\Comment',
            'Oro\Bundle\CallBundle\Entity\Call',
            'call_74d3684c',
            'call_41b3ba7d',
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
