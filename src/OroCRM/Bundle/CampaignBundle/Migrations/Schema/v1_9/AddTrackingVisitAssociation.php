<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;

class AddTrackingVisitAssociation implements Migration, VisitEventAssociationExtensionAwareInterface
{
    /** @var VisitEventAssociationExtension */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension)
    {
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->extension->addVisitEventAssociation($schema, 'orocrm_campaign');
    }
}
