<?php
namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
       
        $cartEmail = $schema->getTable('orocrm_magento_cart_emails');
        $cartEmail->removeForeignKey('FK_11B0F84B1AD5CDBF');
        $cartEmail->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $cartEmail->removeForeignKey('FK_11B0F84BA832C1C9');
        $cartEmail->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $orderEmail = $schema->getTable('orocrm_magento_order_emails');
        $orderEmail->removeForeignKey('FK_10E2A9508D9F6D38');
        $orderEmail->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $orderEmail->removeForeignKey('FK_10E2A950A832C1C9');
        $orderEmail->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }
}
