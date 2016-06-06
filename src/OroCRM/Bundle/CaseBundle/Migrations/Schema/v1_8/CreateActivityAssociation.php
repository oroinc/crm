<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateActivityAssociation implements Migration, ActivityExtensionAwareInterface, NoteExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var NoteExtension */
    protected $noteExtension;

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
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addActivityAssociations($schema, $this->activityExtension);
        self::addNoteAssociations($schema, $this->noteExtension);
    }

    /**
     * Enable activities
     *
     * @param Schema            $schema
     * @param ActivityExtension $activityExtension
     */
    public static function addActivityAssociations(Schema $schema, ActivityExtension $activityExtension)
    {
        $associationTableName = $activityExtension->getAssociationTableName('orocrm_task', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $activityExtension->addActivityAssociation($schema, 'orocrm_task', 'orocrm_case');
        }

        $associationTableName = $activityExtension->getAssociationTableName('oro_calendar_event', 'orocrm_case');
        if (!$schema->hasTable($associationTableName)) {
            $activityExtension->addActivityAssociation($schema, 'oro_calendar_event', 'orocrm_case');
        }
    }

    /**
     * Enable notes for Case entity
     *
     * @param Schema        $schema
     * @param NoteExtension $noteExtension
     */
    public static function addNoteAssociations(Schema $schema, NoteExtension $noteExtension)
    {
        $table = $schema->getTable('oro_note');
        if (!$table->hasColumn($noteExtension->getAssociationColumnName('orocrm_case'))) {
            $noteExtension->addNoteAssociation($schema, 'orocrm_case');
        }
    }
}
