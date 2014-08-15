<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_16;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;

use Doctrine\DBAL\Types\Type;
use Oro\Bundle\EntityBundle\Migrations\MigrateTypesQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMagentoBundle implements Migration, DatabasePlatformAwareInterface
{
    /**
     * @var AbstractPlatform
     */
    protected $platform;

    /**
     * {@inheritdoc}
     */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'oro_integration_channel', 'id', Type::INTEGER)
        );
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'oro_integration_channel_status', 'id', Type::INTEGER)
        );
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'oro_integration_transport', 'id', Type::INTEGER)
        );

        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'orocrm_contactus_contact_rsn', 'id', Type::INTEGER)
        );
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'orocrm_contact_group', 'id', Type::INTEGER)
        );

        $table = $schema->getTable('orocrm_magento_cart');
        $table->getColumn('items_qty')->setType(Type::getType('float'));

        $table = $schema->getTable('orocrm_magento_order_items');
        $table->getColumn('qty')->setType(Type::getType('float'));
    }
}
