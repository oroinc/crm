<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroChannelBundleAssociation;
use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroMarketingCRMBridgeBundle;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtensionAwareInterface;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;

class OroMarketingCRMBridgeBundleInstaller implements
    Installation,
    RenameExtensionAwareInterface,
    IdentifierEventExtensionAwareInterface,
    VisitEventAssociationExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    /** @var RenameExtension */
    protected $renameExtension;

    /** @var IdentifierEventExtension */
    protected $identifierEventExtension;

    /** @var VisitEventAssociationExtension */
    protected $visitExtension;

    /** @var ExtendExtension */
    protected $extendExtension;

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
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addIdentifierEventAssociations($schema);
        $this->addVisitEventAssociation($schema);
        OroMarketingCRMBridgeBundle::updateTrackingVisitEvent($schema, $queries, $this->renameExtension);
        OroMarketingCRMBridgeBundle::updateTrackingVisit($schema, $queries, $this->renameExtension);
        OroChannelBundleAssociation::addChannelForeignKeyToTrackingWebsite($schema, $this->extendExtension);
    }

    /**
     * @param Schema $schema
     */
    protected function addIdentifierEventAssociations(Schema $schema)
    {
        if (!$this->identifierEventExtension->hasIdentifierAssociation($schema, 'orocrm_magento_customer')) {
            $this->identifierEventExtension->addIdentifierAssociation($schema, 'orocrm_magento_customer');
        }
    }

    /**
     * @param Schema $schema
     */
    protected function addVisitEventAssociation(Schema $schema)
    {
        if (!$this->visitExtension->hasVisitEventAssociation($schema, 'orocrm_magento_cart')) {
            $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_cart');
        }
        if (!$this->visitExtension->hasVisitEventAssociation($schema, 'orocrm_magento_customer')) {
            $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_customer');
        }
        if (!$this->visitExtension->hasVisitEventAssociation($schema, 'orocrm_magento_order')) {
            $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_order');
        }
        if (!$this->visitExtension->hasVisitEventAssociation($schema, 'orocrm_magento_product')) {
            $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_product');
        }
    }
}
