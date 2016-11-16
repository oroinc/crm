<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtensionAwareInterface;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;

class OroMagentoBundleAssociation implements
    Migration,
    IdentifierEventExtensionAwareInterface,
    VisitEventAssociationExtensionAwareInterface
{
    /** @var IdentifierEventExtension */
    protected $identifierEventExtension;

    /** @var VisitEventAssociationExtension */
    protected $visitExtension;

    /**
     * {@inheritdoc}
     */
    public function setIdentifierEventExtension(IdentifierEventExtension $identifierEventExtension)
    {
        $this->identifierEventExtension = $identifierEventExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension)
    {
        $this->visitExtension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addIdentifierEventAssociations($schema, $this->identifierEventExtension);
        self::addVisitEventAssociation($schema, $this->visitExtension);
    }

    /**
     * @param Schema $schema
     * @param IdentifierEventExtension $extension
     */
    public static function addIdentifierEventAssociations(Schema $schema, IdentifierEventExtension $extension)
    {
        if (!$extension->hasIdentifierAssociation($schema, 'orocrm_magento_customer')) {
            $extension->addIdentifierAssociation($schema, 'orocrm_magento_customer');
        }
    }

    /**
     * @param Schema $schema
     * @param VisitEventAssociationExtension $extension
     */
    public static function addVisitEventAssociation(Schema $schema, VisitEventAssociationExtension $extension)
    {
        if (!$extension->hasVisitEventAssociation($schema, 'orocrm_magento_cart')) {
            $extension->addVisitEventAssociation($schema, 'orocrm_magento_cart');
        }
        if (!$extension->hasVisitEventAssociation($schema, 'orocrm_magento_customer')) {
            $extension->addVisitEventAssociation($schema, 'orocrm_magento_customer');
        }
        if (!$extension->hasVisitEventAssociation($schema, 'orocrm_magento_order')) {
            $extension->addVisitEventAssociation($schema, 'orocrm_magento_order');
        }
        if (!$extension->hasVisitEventAssociation($schema, 'orocrm_magento_product')) {
            $extension->addVisitEventAssociation($schema, 'orocrm_magento_product');
        }
    }
}
