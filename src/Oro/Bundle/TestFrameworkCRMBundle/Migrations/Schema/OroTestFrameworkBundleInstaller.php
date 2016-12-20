<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;
use Oro\Bundle\TestFrameworkCRMBundle\Migrations\Schema\v1_0\AddCustomerAssociation;

class OroTestFrameworkBundleInstaller implements Installation, CustomerExtensionAwareInterface
{
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        AddCustomerAssociation::addTestCustomerTargetTables($schema);
        AddCustomerAssociation::addCustomerAssociations($schema, $this->customerExtension);
    }
}
