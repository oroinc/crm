<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_0\OroCRMSalesBundle;

class OroCRMSalesBundleInstaller implements Installation
{
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
        OroCRMSalesBundle::orocrmSalesLeadTable($schema);
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
