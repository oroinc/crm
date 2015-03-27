<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_30;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;

class AddTrackingVisitAssociation implements Migration, VisitEventAssociationExtensionAwareInterface
{
    /** @var VisitEventAssociationExtension */
    protected $extension;

    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->extension->addVisitEventAssociation($schema, 'orocrm_magento_cart');
        $this->extension->addVisitEventAssociation($schema, 'orocrm_magento_customer');
        $this->extension->addVisitEventAssociation($schema, 'orocrm_magento_order');
        $this->extension->addVisitEventAssociation($schema, 'orocrm_magento_product');
    }
}
