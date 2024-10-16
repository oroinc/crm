<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateActivityAssociation implements
    Migration,
    OrderedMigrationInterface,
    ActivityExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    use ActivityExtensionAwareTrait;
    use ActivityListExtensionAwareTrait;

    #[\Override]
    public function getOrder(): int
    {
        return 1;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->enableActivityAssociations($schema);

        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_contactus_request');
        $this->activityExtension->addActivityAssociation($schema, 'oro_call', 'orocrm_contactus_request');
        $this->activityListExtension->addActivityListAssociation($schema, 'orocrm_contactus_request');
    }

    private function enableActivityAssociations(Schema $schema): void
    {
        $options = new OroOptions();
        $options->set('activity', 'immutable', false);
        $schema->getTable('orocrm_contactus_request')->addOption(OroOptions::KEY, $options);
    }
}
