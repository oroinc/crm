<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;

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
        static::addEnumValues($queries, $this->extendExtension);

        $queries->addPostQuery(new UpdateTaskStatusQuery($this->extendExtension));

        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'OroCRM\Bundle\TaskBundle\Entity\Task',
                'workflow',
                'show_step_in_grid',
                false
            )
        );
    }

    /**
     * @param Schema          $schema
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

    /**
     * @param QueryBag        $queries
     * @param ExtendExtension $extendExtension
     */
    public static function addEnumValues(QueryBag $queries, ExtendExtension $extendExtension)
    {
        $queries->addPostQuery(new InsertTaskStatusesQuery($extendExtension));
    }
}
