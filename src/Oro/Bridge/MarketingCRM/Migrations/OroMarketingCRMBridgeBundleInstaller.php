<?php

namespace Oro\Bridge\MarketingCRM\Migrations;

use Doctrine\DBAL\Schema\Schema;

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
use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroChannelBundleAssociation;
use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroMarketingCRMBridgeBundle;
use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroMagentoBundleAssociation;

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
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        OroMarketingCRMBridgeBundle::updateTrackingVisitEvent($schema, $queries, $this->renameExtension);
        OroMarketingCRMBridgeBundle::updateTrackingVisit($schema, $queries, $this->renameExtension);
        OroMagentoBundleAssociation::addIdentifierEventAssociations($schema, $this->identifierEventExtension);
        OroMagentoBundleAssociation::addVisitEventAssociation($schema, $this->visitExtension);
        OroChannelBundleAssociation::addChannelForeignKeyToTrackingWebsite($schema, $this->extendExtension);
    }
}
