<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;

class OroCRMMagentoBundle extends Migration implements RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
    protected $renameExtension;

    /**
     * @inheritdoc
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            $this->renameExtension->getRenameTableQuery(
                'orocrm_magento_customer_address',
                'orocrm_magento_customer_addr'
            )
        );
        $queries->addQuery(
            $this->renameExtension->getRenameTableQuery(
                'orocrm_magento_customer_address_to_address_type',
                'orocrm_magento_cust_addr_type'
            )
        );
        $queries->addQuery(
            $this->renameExtension->getRenameTableQuery(
                'orocrm_magento_product_to_website',
                'orocrm_magento_prod_to_website'
            )
        );
    }
}
