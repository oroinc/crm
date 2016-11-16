<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\InstallerBundle\Migration\UpdateExtendRelationQuery;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMarketingCRMBridgeBundle implements Migration, RenameExtensionAwareInterface
{
    /** @var RenameExtension */
    private $renameExtension;

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
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateTrackingVisitEvent($schema, $queries, $this->renameExtension);
    }

    /**
     * @param Schema $schema
     * @param QueryBag $queries
     */
    public static function updateTrackingVisitEvent(Schema $schema, QueryBag $queries, RenameExtension $extension)
    {
        $attachments = $schema->getTable('oro_tracking_visit_event');

        $attachments->removeForeignKey('FK_B39EEE8F218EECB4');
        $extension->renameColumn($schema, $queries, $attachments, 'campaign_cb6118ed_id', 'campaign_a14160a8_id');
        $extension->addForeignKeyConstraint(
            $schema,
            $queries,
            'oro_tracking_visit_event',
            'orocrm_campaign',
            ['campaign_a14160a8_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $queries->addQuery(new UpdateExtendRelationQuery(
            'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
            'Oro\Bundle\CampaignBundle\Entity\Campaign',
            'campaign_cb6118ed',
            'campaign_a14160a8',
            RelationType::MANY_TO_ONE
        ));
    }
}
