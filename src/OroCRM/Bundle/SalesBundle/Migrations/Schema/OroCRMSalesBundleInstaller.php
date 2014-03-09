<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_0\OroCRMSalesBundle;

class OroCRMSalesBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        OroCRMSalesBundle::orocrmSalesLeadTable($schema, $this->extendExtension);
        OroCRMSalesBundle::orocrmSalesLeadStatusTable($schema);
        OroCRMSalesBundle::orocrmSalesOpportunityTable($schema);
        OroCRMSalesBundle::orocrmSalesOpportunityCloseReasonTable($schema, 'orocrm_sales_opport_close_rsn');
        OroCRMSalesBundle::orocrmSalesOpportunityStatusTable($schema, 'orocrm_sales_opport_status');
        OroCRMSalesBundle::orocrmSalesFunnelTable($schema);

        OroCRMSalesBundle::orocrmSalesLeadForeignKeys($schema);
        OroCRMSalesBundle::orocrmSalesOpportunityForeignKeys(
            $schema,
            'orocrm_sales_opport_close_rsn',
            'orocrm_sales_opport_status'
        );
        OroCRMSalesBundle::orocrmSalesFunnelForeignKeys($schema);
    }
}
