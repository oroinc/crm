<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\SalesBundle\Migration\Extension\Customers\LeadExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\Customers\LeadExtensionTrait;
use Oro\Bundle\SalesBundle\Migration\Extension\Customers\OpportunityExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\Customers\OpportunityExtensionTrait;


class OroMagentoBundle implements Migration, OpportunityExtensionAwareInterface, LeadExtensionAwareInterface
{
    use OpportunityExtensionTrait;
    use LeadExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->leadExtension->addLeadCustomerAssociation($schema, 'orocrm_magento_customer', 'id');
        $this->opportunityExtension->addOpportunityCustomerAssociation($schema, 'orocrm_magento_customer', 'id');
    }
}

