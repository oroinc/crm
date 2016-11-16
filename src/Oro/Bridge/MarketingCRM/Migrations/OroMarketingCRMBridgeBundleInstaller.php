<?php

namespace Oro\Bridge\MarketingCRM\Migrations;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroMarketingCRMBridgeBundle;
use Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0\OroMagentoBundleAssociation;
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
    VisitEventAssociationExtensionAwareInterface
{
    /** @var RenameExtension */
    private $renameExtension;

    /** @var IdentifierEventExtension */
    protected $identifierEventExtension;

    /** @var VisitEventAssociationExtension */
    protected $visitExtension;

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
        OroMagentoBundleAssociation::addIdentifierEventAssociations($schema, $this->identifierEventExtension);
        OroMagentoBundleAssociation::addVisitEventAssociation($schema, $this->visitExtension);
    }
}
