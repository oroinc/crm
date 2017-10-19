<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddRestToken implements Migration, RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
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
        $table = $schema->getTable('oro_integration_transport');

        if (!$table->hasColumn('api_token')) {
            $table->addColumn('api_token', 'string', ['notnull' => false, 'length' => 255]);
        }

        if ($table->hasColumn('wsdl_url')) {
            $this->renameExtension->renameColumn($schema, $queries, $table, 'wsdl_url', 'api_url');
        }
    }
}
