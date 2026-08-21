<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_47;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Enables webhook access for the Lead and Opportunity entities on existing installations.
 *
 * The value is updated only where webhooks are currently disabled, so an entity an administrator
 * has already enabled is left untouched.
 */
class EnableWebhookAccess implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addPostQuery(new UpdateEntityConfigEntityValueQuery(
            Lead::class,
            'integration',
            'webhook_accessible',
            true,
            false
        ));

        $queries->addPostQuery(new UpdateEntityConfigEntityValueQuery(
            Opportunity::class,
            'integration',
            'webhook_accessible',
            true,
            false
        ));
    }
}
