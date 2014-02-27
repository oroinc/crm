<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function up(Schema $schema)
    {
        OroCRMSalesBundle::orocrmSalesLeadTable($schema);
        OroCRMSalesBundle::orocrmSalesLeadStatusTable($schema);
        OroCRMSalesBundle::orocrmSalesOpportunityTable($schema);
        OroCRMSalesBundle::orocrmSalesOpportunityCloseReasonTable($schema, 'orocrm_sales_opportunity_close');
        OroCRMSalesBundle::orocrmSalesOpportunityStatusTable($schema, 'orocrm_sales_opportunity_stat');
        OroCRMSalesBundle::orocrmSalesFunnelTable($schema);

        OroCRMSalesBundle::orocrmSalesLeadForeignKeys($schema);
        OroCRMSalesBundle::orocrmSalesOpportunityForeignKeys(
            $schema,
            'orocrm_sales_opportunity_close',
            'orocrm_sales_opportunity_stat'
        );
        OroCRMSalesBundle::orocrmSalesFunnelForeignKeys($schema);

        return [];
    }
}
