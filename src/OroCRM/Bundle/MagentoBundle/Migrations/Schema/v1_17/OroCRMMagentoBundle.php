<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_17;

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
        /** placed here to load in last order */
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'orocrm_contactus_contact_rsn', 'id', Type::INTEGER)
        );
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'orocrm_contact_group', 'id', Type::INTEGER)
        );

        /** override platform queries */
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'oro_integration_channel', 'id', Type::INTEGER)
        );
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'oro_integration_channel_status', 'id', Type::INTEGER)
        );
        $queries->addQuery(
            new MigrateTypesQuery($this->platform, $schema, 'oro_integration_transport', 'id', Type::INTEGER)
        );

        $queries->addQuery(new MigrateTypesQuery($this->platform, $schema, 'oro_access_group', 'id', Type::INTEGER));
        $queries->addQuery(new MigrateTypesQuery($this->platform, $schema, 'oro_access_role', 'id', Type::INTEGER));
        $queries->addQuery(new MigrateTypesQuery($this->platform, $schema, 'oro_user_email', 'id', Type::INTEGER));
        $queries->addQuery(new MigrateTypesQuery($this->platform, $schema, 'oro_user_status', 'id', Type::INTEGER));

        $table = $schema->getTable('orocrm_magento_customer');
        $table->changeColumn('vat', ['type' => Type::getType('float'), 'notnull' => true]);
        $table->changeColumn('vat', ['type' => Type::getType('float'), 'notnull' => true]);
        $table->dropIndex('unq_origin_id_channel_id');
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'magecustomer_oid_cid_unq');
    }
}
