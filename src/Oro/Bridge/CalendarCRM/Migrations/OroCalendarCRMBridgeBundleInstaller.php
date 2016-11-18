<?php

namespace Oro\Bridge\CalendarCRM\Migrations;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bridge\CalendarCRM\Migrations\Schema\v1_0\OroCalendarBridgeBundle as OroCalendarBridgeBundle_v1_0;
use Oro\Bridge\CalendarCRM\Migrations\Schema\v1_1\OroCalendarBridgeBundle as OroCalendarBridgeBundle_v1_1;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCalendarCRMBridgeBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    RenameExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var RenameExtension */
    protected $renameExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

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
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        OroCalendarBridgeBundle_v1_0::addCalendarActivityAssociations($schema, $this->activityExtension);
        OroCalendarBridgeBundle_v1_1::renameActivityTables($schema, $queries, $this->renameExtension);
    }
}
