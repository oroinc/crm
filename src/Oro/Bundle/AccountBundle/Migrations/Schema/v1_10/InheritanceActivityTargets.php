<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InheritanceActivityTargets implements Migration, ActivityListExtensionAwareInterface
{
    use ActivityListExtensionAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->activityListExtension->addInheritanceTargets($schema, 'orocrm_account', 'orocrm_contact', ['accounts']);
    }
}
