<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration, ActivityListExtensionAwareInterface
{
    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addActivityListAssociations($schema, $this->activityListExtension);
    }

    public static function addActivityListAssociations(Schema $schema, ActivityListExtension $activityListExtension)
    {
        $activityListExtension->addActivityListAssociation($schema, 'orocrm_account');
        $activityListExtension->addActivityListAssociation($schema, 'orocrm_contact');
    }
}
