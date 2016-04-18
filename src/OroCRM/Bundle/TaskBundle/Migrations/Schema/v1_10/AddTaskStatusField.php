<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;

class AddTaskStatusField implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        static::addTaskStatusField($schema, $this->extendExtension);

        $queries->addPostQuery(
            'UPDATE orocrm_task AS t
                LEFT JOIN `oro_workflow_step` ws ON t.`workflow_step_id` = ws.id
                INNER JOIN `oro_enum_task_status` ts ON ws.name = ts.id
            SET t.`status_id` = ts.id'
        );
    }

    /**
     * @param Schema $schema
     * @param ExtendExtension $extendExtension
     */
    public static function addTaskStatusField(Schema $schema, ExtendExtension $extendExtension)
    {
        $enumTable = $extendExtension->addEnumField(
            $schema,
            'orocrm_task',
            'status',
            'task_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', ['open', 'in_progress', 'closed']);

        $enumTable->addOption(OroOptions::KEY, $options);
    }
}
