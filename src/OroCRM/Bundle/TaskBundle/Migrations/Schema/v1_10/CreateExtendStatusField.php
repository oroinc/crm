<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;

class CreateExtendedStatusField implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

    /**
     * {@inheritDoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * Modifies the given schema to apply necessary changes of a database
     * The given query bag can be used to apply additional SQL queries before and after schema changes
     *
     * @param Schema $schema
     * @param QueryBag $queries
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'orocrm_task',
            'status',
            'task_status'
        );

        $options = new OroOptions();
        $options->set('enum_codes', 'immutable', ['open', 'in_progress', 'closed']);

        $enumTable->addOption(OroOptions::KEY, $options);

        $queries->addPostQuery(
            'UPDATE orocrm_task AS t
                LEFT JOIN `oro_workflow_step` ws ON t.`workflow_step_id` = ws.id
                INNER JOIN `oro_enum_task_status` ts ON ws.name = ts.id
            SET t.`status_id` = ts.id'
        );
    }
}
